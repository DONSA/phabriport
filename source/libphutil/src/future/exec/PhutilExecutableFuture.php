<?php

/**
 * @task config Configuring the Command
 */
abstract class PhutilExecutableFuture extends Future {


  private $env;
  private $cwd;


  /**
   * Set environmental variables for the command.
   *
   * By default, variables are added to the environment of this process. You
   * can optionally wipe the environment and pass only the specified values.
   *
   *   // Env will have "X" and current env ("PATH", etc.)
   *   $exec->setEnv(array('X' => 'y'));
   *
   *   // Env will have ONLY "X".
   *   $exec->setEnv(array('X' => 'y'), $wipe_process_env = true);
   *
   * @param map<string, string> Dictionary of environmental variables.
   * @param bool Optionally, pass `true` to replace the existing environment.
   * @return this
   *
   * @task config
   */
  final public function setEnv(array $env, $wipe_process_env = false) {
    // Force values to strings here. The underlying PHP commands get upset if
    // they are handed non-string values as environmental variables.
    foreach ($env as $key => $value) {
      $env[$key] = (string)$value;
    }

    if (!$wipe_process_env) {
      $env = $env + $this->getEnv();
    }

    $this->env = $env;

    return $this;
  }


  /**
   * Set the value of a specific environmental variable for this command.
   *
   * @param string Environmental variable name.
   * @param string|null New value, or null to remove this variable.
   * @return this
   * @task config
   */
  final public function updateEnv($key, $value) {
    $env = $this->getEnv();

    if ($value === null) {
      unset($env[$key]);
    } else {
      $env[$key] = (string)$value;
    }

    $this->env = $env;

    return $this;
  }


  /**
   * Returns `true` if this command has a configured environment.
   *
   * @return bool True if this command has an environment.
   * @task config
   */
  final public function hasEnv() {
    return ($this->env !== null);
  }


  /**
   * Get the configured environment.
   *
   * @return map<string, string> Effective environment for this command.
   * @task config
   */
  final public function getEnv() {
    if (!$this->hasEnv()) {
      $this->setEnv($_ENV, $wipe_process_env = true);
    }

    return $this->env;
  }


  /**
   * Set the current working directory for the subprocess (that is, set where
   * the subprocess will execute). If not set, the default value is the parent's
   * current working directory.
   *
   * @param string Directory to execute the subprocess in.
   * @return this
   * @task config
   */
  final public function setCWD($cwd) {
    $cwd = (string)$cwd;

    if (!is_dir($cwd)) {
      throw new Exception(
        pht(
          'Preparing to run a command in directory "%s", but that '.
          'directory does not exist.',
          $cwd));
    }

    $this->cwd = $cwd;

    return $this;
  }


  /**
   * Get the command's current working directory.
   *
   * @return string Working directory.
   * @task config
   */
  final public function getCWD() {
    return $this->cwd;
  }

}
