import os
import sys
import datetime
sys.path.append(os.path.dirname(os.path.abspath(__file__)) + '/..')
os.environ['DJANGO_SETTINGS_MODULE'] = 'vlab.settings'
os.environ['AWS_ACCESS_KEY_ID'] = ''
os.environ['AWS_SECRET_ACCESS_KEY'] = ''

from api.models import *
from api.views import *

five_minutes= 'La maquina creada para su practica de oracle se parara dentro de 5 minutos'

# Primero comprobamos lo de los 5 minutos.
instances = Instance.objects.filter(real_termination_date__exact=None).filter(estimated_termination_date__lte= datetime.now()+timedelta(minutes=5)).filter(five_minutes_notified__exact='n')

for instance in instances:
    #Notificamos  que quedan 5 minutos para parar la maquina.
    request=Request.objects.get(ext_id=instance.request)
    print request.email
    msg = MIMEText(five_minutes)
    msg['Subject'] = "La maquina se parara en 5 minutos"
    msg['From'] = 'admin@didakos.com'
    msg['Date'] = formatdate(localtime=True)
    msg['To'] = request.email
    smtp = smtplib.SMTP('mail')
    #smtp.sendmail('admin@didakos.com', request.email, msg.as_string())
    smtp.close()
    instance.five_minutes_notified='y'
    instance.save()
    print instance.request

# Si ya ha pasado el tiempo, paramos la maquina
instances = Instance.objects.filter(real_termination_date__exact=None).filter(estimated_termination_date__lte= datetime.now())
for instance in instances:
    print instance.request
    stop('','stop',instance.request)

