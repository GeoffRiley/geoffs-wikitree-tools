#!/usr/bin/python
import sys
import cgitb

sys.path.append('/www/lib/')

cgitb.enable()

print("Content-Type: text/html;\n")

print("<html><body><h2>Testing!</h2><pre>"+sys.version+"</pre>")

try:
    import pywikitree
except:
    print("<p style='color:red;'>package pywikitree not found</p>")
else:
    print("<p style='color:green;'>package pywikitree found</p>")

print("</body></html>")
