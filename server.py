from http.server import HTTPServer, SimpleHTTPRequestHandler
import os

class Handler(SimpleHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/' or self.path == '/index.html':
            self.send_response(200)
            self.send_header('Content-type', 'text/html')
            self.end_headers()
            with open('index.html', 'rb') as f:
                self.wfile.write(f.read())
        else:
            super().do_GET()

os.chdir(os.path.dirname(os.path.abspath(__file__)))
server = HTTPServer(('0.0.0.0', 5000), Handler)
print("Project Analysis Server running on http://0.0.0.0:5000")
server.serve_forever()
