#!/usr/bin/env python
"""
stream2chromecast.py: Chromecast media streamer for Linux

author: Pat Carter - https://github.com/Pat-Carter/stream2chromecast

version: 0.6.3

"""


# Copyright (C) 2014-2016 Pat Carter
#
# This file is part of Stream2chromecast.
#
# Stream2chromecast is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Stream2chromecast is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Stream2chromecast.  If not, see <http://www.gnu.org/licenses/>.

VERSION = "0.6.3"

import sys, os, errno
import signal
import time
import BaseHTTPServer
import urllib
from threading import Thread
import subprocess
import httplib
import urlparse
import socket
import tempfile

script_name = (sys.argv[0].split(os.sep))[-1]
PIDFILE = os.path.join(tempfile.gettempdir(), "stream2chromecast_%s.pid") 
FFMPEG = 'ffmpeg %s -i "%s" -preset ultrafast -f mp4 -frag_duration 3000 -b:v 2000k -loglevel error %s -'
AVCONV = 'avconv %s -i "%s" -preset ultrafast -f mp4 -frag_duration 3000 -b:v 2000k -loglevel error %s -'
EXCEPT_NUMBER=0

class RequestHandler(BaseHTTPServer.BaseHTTPRequestHandler):
    content_type = "video/mp4"
    filename = ''
    
    def do_GET(self):
        global EXCEPT_NUMBER
        self.suppress_socket_error_report = None
        self.send_headers()   
        try: 
            self.write_response()
        except socket.error, e:    
            print 'Exeption number : '+str(EXCEPT_NUMBER) 
            if isinstance(e.args, tuple) and  e[0] in (errno.EPIPE, errno.ECONNRESET):
                EXCEPT_NUMBER = EXCEPT_NUMBER + 1
            if EXCEPT_NUMBER > 1 :
                print 'Exeption so exit'
                os._exit(0)

    def handle_one_request(self):
        try:
            return BaseHTTPServer.BaseHTTPRequestHandler.handle_one_request(self)
        except socket.error:
            pass

    def finish(self):
        try:
            return BaseHTTPServer.BaseHTTPRequestHandler.finish(self)
        except socket.error:
            pass

    def send_headers(self):
        self.protocol_version = "HTTP/1.1"
        self.send_response(200)
        self.send_header("Content-type", self.content_type)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header("Transfer-Encoding", "chunked")
        self.end_headers()    

    def write_response(self):
        with open(self.filepath, "rb") as f:           
            while True:
                line = f.read(1024)
                if len(line) == 0:
                    break
                chunk_size = "%0.2X" % len(line)
                self.wfile.write(chunk_size)
                self.wfile.write("\r\n")
                self.wfile.write(line) 
                self.wfile.write("\r\n")  
        self.wfile.write("0")
        self.wfile.write("\r\n\r\n")    

class TranscodingRequestHandler(RequestHandler):
    transcoder_command = FFMPEG
    transcode_options = ""
    transcode_input_options = ""    
    bufsize = 0           

    def write_response(self):
        ffmpeg_command =self.transcoder_command % (self.transcode_input_options,self.filename, self.transcode_options) 
        ffmpeg_process = subprocess.Popen(ffmpeg_command, stdout=subprocess.PIPE, shell=True, bufsize=self.bufsize)    
        for line in ffmpeg_process.stdout:
            chunk_size = "%0.2X" % len(line)
            self.wfile.write(chunk_size)
            self.wfile.write("\r\n")
            self.wfile.write(line) 
            self.wfile.write("\r\n")            
        self.wfile.write("0")
        self.wfile.write("\r\n\r\n")

class SubRequestHandler(RequestHandler):
    content_type = "text/vtt;charset=utf-8"
            
def get_transcoder_cmds():
    probe_cmd = None
    transcoder_cmd = None
    ffmpeg_installed = is_transcoder_installed("ffmpeg")
    avconv_installed = is_transcoder_installed("avconv")  
    if avconv_installed:
        transcoder_cmd = "avconv"
        probe_cmd = "avprobe"
    elif ffmpeg_installed:
        print "unable to find avconv - using ffmpeg"
        transcoder_cmd = "ffmpeg"
        probe_cmd = "ffprobe"
    return transcoder_cmd, probe_cmd           

def is_transcoder_installed(transcoder_application):
    try:
        subprocess.check_output([transcoder_application, "-version"])
        return True
    except OSError:
        return False

def play(filename,folder): 
    transcoder_cmd, probe_cmd = get_transcoder_cmds()
    webserver_ip = [l for l in ([ip for ip in socket.gethostbyname_ex(socket.gethostname())[2] if not ip.startswith("127.")][:1], [[(s.connect(('8.8.8.8', 53)), s.getsockname()[0], s.close()) for s in [socket.socket(socket.AF_INET, socket.SOCK_DGRAM)]][0][1]]) if l][0][0]
    req_handler = RequestHandler
    if transcoder_cmd in ("ffmpeg", "avconv"):
        req_handler = TranscodingRequestHandler
        if transcoder_cmd == "ffmpeg":  
            req_handler.transcoder_command = FFMPEG
        else:
            req_handler.transcoder_command = AVCONV  
        req_handler.bufsize = 0
    else:
        print "No transcoder is installed. Attempting standard playback"
    req_handler.filename = filename
    server = BaseHTTPServer.HTTPServer((webserver_ip, 0), req_handler)
    file(folder+'/camera_stream', 'w').write("http://%s:%s" % (webserver_ip, str(server.server_port)))
    print "http://%s:%s" % (webserver_ip, str(server.server_port))
    server.serve_forever()
    
def run():
    args = sys.argv[1:] 
    play(args[0],args[1])                         
            
if __name__ == "__main__":
    run()