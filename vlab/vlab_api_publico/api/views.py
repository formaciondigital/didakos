import os
import boto.ec2
import boto.exception
from django.http import HttpResponse
from django.db import IntegrityError
from django.db.models import Q
from vlab_api_publico.api.models import *
from django.shortcuts import render_to_response
from django.core import serializers
from django.utils import simplejson
from datetime import datetime, timedelta
import email 
from email.MIMEText import MIMEText
from email.Utils import formatdate
import smtplib

#boto.config.add_section('Boto')
#boto.config.set('Boto', 'proxy', '')
#boto.config.set('Boto', 'proxy_port', '')

max_allowed_time = 235
min_allowed_time = 15
max_price = 1
max_requests_per_region = 190
email_launched="Estimado alumno.\n\n"
email_launched+="La maquina virtual que ha solicitado para realizar su practica ya esta disponible.\n\n"
email_launched+="Los datos de acceso son los siguentes:\n"
email_launched+="IP:\ninstance_ip\n\n"
email_launched+="Clave privada:\ninstance_keypair"
email_stopped="Estimado alumno.\n"
email_stopped+="La maquina virtual que habia solicitado para sus practicas de oracle ya no esta disponible."


def send_error(metodo,reason,ec2_response):
    text="Se ha producido un error en el metodo %s\n El reason comunicado es %s. \n El ec2_response es %s" % (metodo,reason,ec2_response)
    receiver = ['admin@didakos.com']
    sender = 'vlab@didakos.com'
    msg =  "From: %s\n" % sender
    msg += "To: %s\n" % (", ".join(receiver))
    msg += "Subject: VLAB ERROR\n\n"
    msg +=  text
    smtp = smtplib.SMTP('mail')
    #smtp.sendmail('vlab@didakos.com', receiver , msg)
    smtp.close()

def launched(request, type, ext_id):
    """
    Instance ready notification (sent by the instance itself)

    :param request: Request object
    :type request: Request object
    :param type: operation type (launched in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :returns: Request status (OK, ERROR, etc) 
    :rtype: HttpResponse object (json output)
    """
    # *** Check reverse DNS!!! ***
    status = 'OK'
    reason = ''
    ec2_response=''
    # Check if ext_id exists
    try:
        request = Request.objects.get(ext_id=ext_id)
        request.completed = True
        request.save()
    except Request.DoesNotExist:
        status, reason = 'ERROR', 'PETICION_ID does not exist'
    if status == 'OK': 
        # Check if we have received previously this notification
        instance = None
        try:
            instance = Instance.objects.get(request__ext_id=request.ext_id)
        except Instance.DoesNotExist:
            pass
        if not instance:
            # Get new instance created from Amazon to save its information in the db   
            conn = boto.ec2.get_region(request.region.name).connect()
            try:
                instances_list = conn.get_all_instances()
                for instance in instances_list:
                    if instance.id==request.sir_id:
                         sir=instance
            except boto.exception.EC2ResponseError, err:
                ec2_response=str(err)
                status, reason = 'ERROR', 'EC2ResponseError on get_all_spot_instance_requests -contact sergio-'
            if status == 'OK':
                keypair = Keypair.objects.get(name=ext_id)
                ami = AMI.objects.get(virtual_ami__name=request.virtual_ami.name, region=keypair.region)
                try:
                    i = conn.get_all_instances([sir.instances[0].id])[0].instances[0]
                except boto.exception.EC2ResponseError,err:
                    ec2_response=str(err)
                    status, reason = 'ERROR', 'EC2ResponseError on get_all_instances -contact sergio-'
                if status == 'OK':
                    instance = Instance(instance_id=i.id, state=i.state, type=i.instance_type, estimated_termination_date=datetime.now()+timedelta(minutes=int(request.time)),public_dns_name=i.public_dns_name, keypair=keypair, ami=ami, request=request)
                    try:
                        instance.save()
                    except IntegrityError:
                        # Never should enter here (controlled before)
                        status, reason = 'ERROR', 'Instance launch notification already received'
                    # This would be a good place to launch the external notification
                    text=email_launched.replace("instance_ip",instance.public_dns_name).replace('instance_keypair',keypair.key)
                    msg = MIMEText(text)
                    msg['Subject'] = "La maquina se ha lanzado"
                    msg['From'] = 'vlab@didakos.com'
                    msg['Date'] = formatdate(localtime=True)
                    msg['To'] = request.email
                    smtp = smtplib.SMTP('mail')
                    #smtp.sendmail('vlab@didakos.com', request.email, msg.as_string())
                    smtp.close()
                    msg['To'] = "admin@didakos.com"
                    smtp = smtplib.SMTP('mail')
                    #smtp.sendmail('vlab@didakos.com', "admin@didakos.com", msg.as_string())
                    smtp.close()

        else:
            status, reason = 'ERROR', 'Instance launch notification already received'
    if status !='OK':
        send_error('launched',reason,ec2_response)

    return HttpResponse(simplejson.dumps({'status':status, 'reason':reason}))
 
def stopped(request, type, ext_id):
    """
    Instance stopped notification (sent by the instance itself)

    :param request: Request object
    :type request: Request object
    :param type: operation type (stopped in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :returns: Request status (OK, ERROR, etc) 
    :rtype: HttpResponse object (json output)
    """
    # *** Check reverse DNS!!! ***
    status = 'OK'
    reason = ''
    ec2_response = ''
    # Check if ext_id exists
    try:
        request = Request.objects.get(ext_id=ext_id)
    except Request.DoesNotExist:
        status, reason = 'ERROR', 'PETICION_ID does not exist'
    if status == 'OK': 
        try:
            instance = Instance.objects.get(request=request)
            if instance.real_termination_date:
                status, reason = 'ERROR', 'Instance stopped notification already received'
        except Request.DoesNotExist:
            status, reason = 'ERROR', 'This PETICION_ID has no instance associated'
        if status == 'OK':
            instance.real_termination_date = datetime.now()
            instance.state = 'terminated'
            instance.save()
    if status !='OK':
        send_error('stopped',reason,ec2_response)

    return HttpResponse(simplejson.dumps({'status':status, 'reason':reason}))
 
def imalive(request, type, ext_id):
    """
    Instance imalive notification (sent by the instance itself)

    :param request: Request object
    :type request: Request object
    :param type: operation type (imalive in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :returns: Request status (OK, ERROR, etc)
    :rtype: HttpResponse object (json output)
    """
    # *** Check reverse DNS!!! ***
    status = 'OK'
    reason = ''
    ec2_response = ''
    # Check if ext_id exists
    try:
        request = Request.objects.get(ext_id=ext_id)
    except Request.DoesNotExist:
        status, reason = 'ERROR', 'PETICION_ID does not exist'
    if status == 'OK':
        try:
            instance = Instance.objects.get(request=request)
        except Request.DoesNotExist:
            status, reason = 'ERROR', 'This PETICION_ID has no instance associated'
        if status == 'OK':
            instance.last_imalive_date = datetime.now()
            instance.save()
    if status !='OK':
        send_error('imalive',reason,ec2_response)
    return HttpResponse(simplejson.dumps({'status':status, 'reason':reason}))

