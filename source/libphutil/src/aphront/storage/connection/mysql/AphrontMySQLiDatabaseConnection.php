<?php

/**
 * @phutil-external-symbol class mysqli
 */
final class AphrontMySQLiDatabaseConnection
  extends AphrontBaseMySQLDatabaseConnection {

  public function escapeUTF8String($string) {
    $this->validateUTF8String($string);
    return $this->escapeBinaryString($string);
  }

  public function escapeBinaryString($string) {
    return $this->requireConnection()->escape_string($string);
  }

  public function getInsertID() {
    return $this->requireConnection()->insert_id;
  }

  public function getAffectedRows() {
    return $this->requireConnection()->affected_rows;
  }

  protected function closeConnection() {
    $this->requireConnection()->close();
  }

  protected function connect() {
    if (!class_exists('mysqli', false)) {
      throw new Exception(pht(
        'About to call new %s, but the PHP MySQLi extension is not available!',
        'mysqli()'));
    }

    $user = $this->getConfiguration('user');
    $host = $this->getConfiguration('host');
    $port = $this->getConfiguration('port');
    $database = $this->getConfiguration('database');

    $pass = $this->getConfiguration('pass');
    if ($pass instanceof PhutilOpaqueEnvelope) {
      $pass = $pass->openEnvelope();
    }

    // If the host is "localhost", the port is ignored and mysqli attempts to
    // connect over a socket.
    if ($port) {
      if ($host === 'localhost' || $host === null) {
        $host = '127.0.0.1';
      }
    }

    $conn = mysqli_init();

    $timeout = $this->getConfiguration('timeout');
    if ($timeout) {
      $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
    }

    @$conn->real_connect(
      $host,
      $user,
      $pass,
      $database,
      $port);

    $errno = $conn->connect_errno;
    if ($errno) {
      $error = $conn->connect_error;
      $this->throwConnectionException($errno, $error, $user, $host);
    }

    $ok = @$conn->set_charset('utf8mb4');
    if (!$ok) {
      $ok = $conn->set_charset('utf8');
    }

    return $conn;
  }

  protected function rawQuery($raw_query) {
    $conn = $this->requireConnection();
    $time_limit = $this->getQueryTimeout();

    // If we have a query time limit, run this query synchronously but use
    // the async API. This allows us to kill queries which take too long
    // without requiring any configuration on the server side.
    if ($time_limit && $this->supportsAsyncQueries()) {
      $conn->query($raw_query, MYSQLI_ASYNC);

      $read = array($conn);
      $error = array($conn);
      $reject = array($conn);

      $result = mysqli::poll($read, $error, $reject, $time_limit);

      if ($result === false) {
        $this->closeConnection();
        throw new Exception(
          pht('Failed to poll mysqli connection!'));
      } else if ($result === 0) {
        $this->closeConnection();
        throw new AphrontQueryTimeoutQueryException(
          pht(
            'Query timed out after %s second(s)!',
            new PhutilNumber($time_limit)));
      }

      return @$conn->reap_async_query();
    }

    return @$conn->query($raw_query);
  }

  protected function rawQueries(array $raw_queries) {
    $conn = $this->requireConnection();

    $have_result = false;
    $results = array();

    foreach ($raw_queries as $key => $raw_query) {
      if (!$have_result) {
        // End line in front of semicolon to allow single line comments at the
        // end of queries.
        $have_result = $conn->multi_query(implode("\n;\n\n", $raw_queries));
      } else {
        $have_result = $conn->next_result();
      }

      array_shift($raw_queries);

      $result = $conn->store_result();
      if (!$result && !$this->getErrorCode($conn)) {
        $result = true;
      }
      $results[$key] = $this->processResult($result);
    }

    if ($conn->more_results()) {
      throw new Exception(
        pht('There are some results left in the result set.'));
    }

    return $results;
  }

  protected function freeResult($result) {
    $result->free_result();
  }

  protected function fetchAssoc($result) {
    return $result->fetch_assoc();
  }

  protected function getErrorCode($connection) {
    return $connection->errno;
  }

  protected function getErrorDescription($connection) {
    return $connection->error;
  }

  public function supportsAsyncQueries() {
    return defined('MYSQLI_ASYNC');
  }

  public function asyncQuery($raw_query) {
    $this->checkWrite($raw_query);
    $async = $this->beginAsyncConnection();
    $async->query($raw_query, MYSQLI_ASYNC);
    return $async;
  }

  public static function resolveAsyncQueries(array $conns, array $asyncs) {
    assert_instances_of($conns, __CLASS__);
    assert_instances_of($asyncs, 'mysqli');

    $read = $error = $reject = array();
    foreach ($asyncs as $async) {
      $read[] = $error[] = $reject[] = $async;
    }

    if (!mysqli::poll($read, $error, $reject, 0)) {
      return array();
    }

    $results = array();
    foreach ($read as $async) {
      $key = array_search($async, $asyncs, $strict = true);
      $conn = $conns[$key];
      $conn->endAsyncConnection($async);
      $results[$key] = $conn->processResult($async->reap_async_query());
    }
    return $results;
  }

}
