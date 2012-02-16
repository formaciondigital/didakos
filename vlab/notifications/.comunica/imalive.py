import pycurl
from StringIO import StringIO
for linea in open('/root/.ssh/authorized_keys','r').readlines():
    id=linea.split(' ')[2]
id=id.replace("\n","")
c = pycurl.Curl()
c.setopt(c.URL, 'https://api.didakos.com/vlab/api/imalive/%s/'%(id))
c.perform()
c.close()
