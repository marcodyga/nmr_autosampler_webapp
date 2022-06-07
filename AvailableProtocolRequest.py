"""
This script will call the AvailableProtocolRequest and return an XML code
describing all the protocols and options available to your Spinsolve 
spectrometer.
"""

import socket

nmr_ip = "127.0.0.1"
port = 13000

socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
socket.connect((nmr_ip, port))

message = """<?xml version="1.0" encoding="utf-8"?>
<Message>
   <AvailableProtocolOptionsRequest/>
</Message>"""
socket.send(message.encode())
response = socket.recv(65536)
response = response.decode("UTF-8")
print(response)

socket.close()
