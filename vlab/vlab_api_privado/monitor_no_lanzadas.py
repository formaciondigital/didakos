import os
import sys
import datetime
sys.path.append(os.path.dirname(os.path.abspath(__file__)) + '/..')
os.environ['DJANGO_SETTINGS_MODULE'] = 'vlab_api_privado.settings'
os.environ['AWS_ACCESS_KEY_ID'] = ''
os.environ['AWS_SECRET_ACCESS_KEY'] = ''

from api.models import *
from api.views import *


def send_error2( instancia):
    text="La maquina %s fue creada hace mas de 20 minutos y aun no hemos recibido la confirmacion via launched "% (instancia)
    #receiver = ['admin@didakos.com']
    #sender = 'vlab@didakos.com'
    msg =  "From: %s\n" % sender
    msg += "To: %s\n" % (", ".join(receiver))
    msg += "Subject: VLAB ERROR\n\n"
    msg +=  text
    smtp = smtplib.SMTP('mail')
    #smtp.sendmail('admin@didakos.com', receiver , msg)
    smtp.close()


requests = Request.objects.filter(creation_date__lte=datetime.now()-timedelta(minutes=20)).filter(completed__exact=False).filter(launch_error_notified__exact='n')

for request in requests:
    send_error2(request.ext_id)

    request.launch_error_notified='y'
    request.save()

