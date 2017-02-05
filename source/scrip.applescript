on alfred_script(q)

    set theSender to "Your Name <name@domain.com>"
    set theRecipientName to ""
    set theRecipientAddress to ""
    set theSignatureName to ""

    set array to (q) as string
    set AppleScript's text item delimiters to "|"
    set the itemsArray to every text item of the array
    set theSubject to item 1 of itemsArray
    set theBody to item 2 of itemsArray

    tell application "Mail"
        activate
	set newMessage to make new outgoing message with properties {visible:true, sender:theSender, subject:theSubject, content:theBody}

        tell newMessage
            make new to recipient with properties {name:theRecipientName, address:theRecipientAddress}
        end tell

        try 
            set message signature of newMessage to signature theSignatureName 
	on error
	    tell application "Mail" to activate 
	    tell application "System Events" 
	    tell process "Mail" 
	        click pop up button 1 of window 1 
	        delay 0.1 
	        keystroke "" -- First letter of your signature 
	        delay 0.1 
	        keystroke return 
	        delay 0.1 
	    end tell 
            end tell 
     	end try 
     end tell

end alfred_script
