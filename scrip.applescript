on alfred_script(q)

	set theRecipientName to ""
	set theRecipientAddress to ""

	set array to (q) as string
	set AppleScript's text item delimiters to "|"
	set the itemsArray to every text item of the array
	set theSubject to item 1 of itemsArray
	set theBody to item 2 of itemsArray


	tell application "Mail"

		set newMessage to make new outgoing message with properties {subject:theSubject, content:theBody, visible:true}

        tell newMessage
            make new to recipient with properties {name:theRecipientName, address:theRecipientAddress}
        end tell

	end tell

end alfred_script
