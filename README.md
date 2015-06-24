# pycam_php_control
Remote control of pycam motion-detecting Raspberry Pi camera, using PHP on a web server.

Works in conjunction with runner.js in the pycam installation to allow the user to start and stop the pycam remotely 
via a button on the viewing web page.

These files should be placed in the server directory where pycam uploads its snapshots and video captures.
The directory should be password-protected.

The commands sent to the pycam's runner.js service are SSH-encrypted using a 'public' key, which should in fact be kept private.
The corresponding private key needs to be installed in the pycam directory on the raspi, to allow it to decrypt the commands.
