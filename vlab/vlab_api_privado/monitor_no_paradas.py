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
    text="La maquina %s se paro hace 20 minutos y aun no hemos recibido la confirmacion via stopped"%(instancia)
    #receiver = ['admin@didakos.com']
    #sender = 'vlab@didakos.com'
    msg =  "From: %s\n" % sender
    msg += "To: %s\n" % (", ".join(receiver))
    msg += "Subject: VLAB ERROR\n\n"
    msg +=  text
    smtp = smtplib.SMTP('mail')
    #smtp.sendmail('admin@didakos.com', receiver , msg)
    smtp.close()


instances = Instance.objects.filter(real_termination_date__exact=None).filter(estimated_termination_date__lte=datetime.now()-timedelta(minutes=2)).filter(stop_error_notified__exact='n')

for instance in instances:

    request=Request.objects.get(ext_id=instance.request)
    send_error2(request.ext_id)

    instance.stop_error_notified='y'
    instance.save()

