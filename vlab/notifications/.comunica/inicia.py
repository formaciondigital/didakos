import time,sys,httplib, urllib,ssl 
import pyinotify
import pycurl
import os
import smtplib

class ProcessTransientFile(pyinotify.ProcessEvent):
    def process_IN_CREATE(self, event):
        print '\t', event.pathname, ' -> written'
        time.sleep(10)
        for linea in open('/root/.ssh/authorized_keys','r').readlines():
            id=linea.split(' ')[2]
        id=id.replace("\n","")
        c = pycurl.Curl()
        c.setopt(c.URL, 'https://api.didakos.com/vlab/api/launched/%s/'%(id))
        try:
            c.perform()
        except:
            self.process_IN_CREATE(event)
        print "status code: %s" % c.getinfo(pycurl.HTTP_CODE)
        while c.getinfo(pycurl.HTTP_CODE)!=200:
            if not os.path.exists('/root/.comunica/enviado'):
                f=open('/root/.comunica/enviado','w')
                f.write("enviado")
                f.close()  
                contenido='La maquina  tiene el id %s'% (id)
                subject='Maquina Amazon que no puede comunicar el inicio'
                sender = "amazon@grupogdt.com"
                receiver = ['admin@didakos.com']
                msg =  "From: %s\n" % sender
                msg += "To: %s\n" % (", ".join(receiver))
                msg += "Subject:%s\n"  % (subject)
                msg += "Content-Type: text;\n"
                msg += "Mime-Version:   1.0;\n"
                msg +=contenido
                server = smtplib.SMTP('mail')
                server.sendmail(sender, receiver, msg)
                server.quit()
            time.sleep(600)
            c.perform()
        exit()

if __name__ == '__main__':
    wm = pyinotify.WatchManager()
    notifier = pyinotify.Notifier(wm,ProcessTransientFile())
    wdd=wm.add_watch('/root/.ssh', pyinotify.IN_CREATE)
    notifier.loop()
