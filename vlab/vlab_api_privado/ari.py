import os
import sys
import datetime
sys.path.append(os.path.dirname(os.path.abspath(__file__)) + '/..')
os.environ['DJANGO_SETTINGS_MODULE'] = 'vlab.settings'
os.environ['AWS_ACCESS_KEY_ID'] = ''
os.environ['AWS_SECRET_ACCESS_KEY'] = ''

from api.models import *
from api.views import *


parameters = AmiParameters.objects.filter(active=True)
msg=""
conn = boto.ec2.get_region('eu-west-1').connect()
images_eu=conn.get_all_images()
conn = boto.ec2.get_region('us-west-1').connect()
images_us=conn.get_all_images()



for parameter in parameters:
    aki_encontrado=False
    ari_encontrado=False
    for image in images_eu:
        if str(parameter.aki)==str(image.id):
            aki_encontrado=True
        if str(parameter.ari)==str(image.id):
            ari_encontrado=True
    for image in images_us:
        if str(parameter.aki)==str(image.id):
            aki_encontrado=True
        if str(parameter.ari)==str(image.id):
            ari_encontrado=True
    if aki_encontrado==False:
        msg=msg+"El Aki %s no se ha encontrado" % (parameter.aki)
    if ari_encontrado==False:
        msg=msg+"El Ari %s no se ha encontrado" % (parameter.ari)

if msg != "":
    contenido= msg
    subject='VLAB ERROR'
    sender = "admin@didakos.com"
    receiver = ['admin@didakos.com']
    msg =  "From: %s\n" % sender
    msg += "To: %s\n" % (", ".join(receiver))
    msg += "Subject:%s\n"  % (subject)
    msg += "Content-Type: text;\n"
    msg += "Mime-Version:   1.0;\n"
    msg += contenido
    server = smtplib.SMTP('mail')
    #server.sendmail(sender, receiver, msg)
    server.quit()
