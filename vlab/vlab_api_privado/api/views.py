import os
import boto.ec2
import boto.exception
from django.http import HttpResponse
from django.db import IntegrityError
from django.db.models import Q
from vlab_api_privado.api.models import *
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
max_instances_per_region = 190
email_new="Estimado alumno.\n\n"
email_new+="La maquina virtual que ha solicitado para realizar su practica se esta preparando.\n\n"
email_new+="Los datos de acceso son los siguentes:\n"
email_new+="Clave privada:\ninstance_keypair"

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

def new(request, type, ext_id, time, virtual_ami, email, zone=''):
    """
    Launches a new instance 

    :param request: Request object
    :type request: Request object
    :param type: operation type (new in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :param time: Instance life time
    :type time: integer -minutes, 3 digits max-
    :param virtual_ami: Virtual AMI identifier. Will be linked to real ami ids in the different regions
    :type virtual_ami: integer -10 digits-
    :param email: Email
    :type email: string
    :param zone: Zona
    :type zone: string 
    :returns: Request status (OK, ERROR, etc) 
    :rtype: HttpResponse object (json output)
    """
    status = 'OK'
    reason = ''
    keypair = None
    ec2_response=''
    # Check if ext_id doesn't already exists
    try:
        Request.objects.get(ext_id=ext_id)
        status, reason = 'ERROR', 'Duplicate PETICION_ID'   
    except Request.DoesNotExist:
        pass
    if status == 'OK':
        # Check if virtual_ami is valid
        try:
            virtual_ami = VirtualAMI.objects.get(name=virtual_ami)
        except VrtualAMI.DoesNotExist:
            status, reason = 'ERROR', 'MAQUINA_ID not valid'   
        if status == 'OK':
            # We are going to choose the best region/ami for this particular request
            if zone=='':
                regions = Region.objects.all().order_by('priority')
            else:
                regions = Region.objects.filter(name=zone)
            if regions:
                region = None
                ami_not_found_counter = 0
                for r in regions:
                    try:
                        ami = AMI.objects.get(virtual_ami__name=virtual_ami.name, region=r)
                    except AMI.DoesNotExist:
                        ami_not_found_counter += 1
                        continue
                    active_instances_in_region = Instance.objects.filter(ami__region__name__exact=r).exclude(state__exact='terminated')
                    if len(active_instances_in_region) > max_instances_per_region:
                        continue
                    else:
                        region = r
                        break
                if not region:
                    if ami_not_found_counter == len(regions):
                        status, reason = 'ERROR', 'AMI not found for %s -contact sergio-' % virtual_ami.name
                    else:
                        status, reason = 'ERROR', 'All the regions are full now? -contact sergio-'
            else:
                status, reason = 'ERROR', 'Regions table empty -contact sergio-'
            if status == 'OK':
                # Check if time < max_allowed_time
                if int(time) > max_allowed_time or int(time) < min_allowed_time:
                    status, reason = 'ERROR', 'TIME must not exceed %s minutes and must be greater than %s minutes' % (max_allowed_time, min_allowed_time)
                if status == 'OK':
                    # At this point the request is considered valid and we save it
                    # save log to disk first
                    request = Request(request_type=type, ext_id=ext_id, time=time, virtual_ami=virtual_ami, region=region, email=email)
                    request.save()
                    ami_region=ami.region
                    region_name=ami.region.name
                    print ami.region.name
                    conn = boto.ec2.get_region(ami.region.name).connect()
                    # Check if we have a keypair with this (ext_id) name
                    try:
                        keypair = Keypair.objects.get(name=ext_id, region=region)
                    except Keypair.DoesNotExist:
                        pass
                    # If not we create it
                    if not keypair:
                        try:
                            k = conn.create_key_pair(ext_id)
                        except boto.exception.EC2ResponseError,err:
                            ec2_response=str(err)
                            status, reason = 'ERROR', 'EC2ResponseError on request_spot_instances (keypair exists?) -contact sergio-'
                        if status == 'OK':
                            k.save('/tmp')
                            f=open('/tmp/%s.pem' % ext_id, 'r')
                            key = f.read()
                            os.remove('/tmp/%s.pem' % ext_id)
                            keypair = Keypair(name=ext_id,region=region,fingerprint=k.fingerprint,key=key)
                            keypair.save()
                    if status == 'OK':
                        # Now we launch the spot instance request
                        try:
                            #reservation = conn.request_spot_instances(kernel_id=ami.ami_parameters.aki,ramdisk_id=ami.ami_parameters.ari,price=max_price,image_id=ami.name,key_name=keypair.name,security_groups=['oracle-demostracion'])
                            reservation = conn.run_instances(kernel_id=ami.ami_parameters.aki,ramdisk_id=ami.ami_parameters.ari,image_id=ami.name,key_name=keypair.name,security_groups=['oracle-demostracion'])
                            text=email_new.replace('instance_keypair',key)
                            msg = MIMEText(text)
                            msg['Subject'] = "La maquina se esta preparando"
                            msg['From'] = 'vlab@didakos.com'
                            msg['Date'] = formatdate(localtime=True)
                            msg['To'] = email
                            smtp = smtplib.SMTP('mail')
                            #smtp.sendmail('vlab@didakos.com', email, msg.as_string())
                            smtp.close()
                            smtp = smtplib.SMTP('mail')
                            #smtp.sendmail('vlab@didakos.com', "admin@didakos.com", msg.as_string())
                            smtp.close()
                            request.sir_id = reservation.id
                            request.save()
                        except boto.exception.EC2ResponseError, err:
                            ec2_response=str(err)
                            status, reason = 'ERROR', 'EC2ResponseError on request_spot_instances -contact sergio-'
    if status !='OK':
        send_error('start',reason,ec2_response)

    return HttpResponse(simplejson.dumps({'status':status, 'reason':reason}))

def stop(request, type, ext_id):
    """
    Stops a running instance 

    :param request: Request object
    :type request: Request object
    :param type: operation type (stop in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :returns: Request status (OK, ERROR, etc) 
    :rtype: HttpResponse object (json output)
    """
    status = 'OK'
    reason = ''
    # Check if ext_id exists
    try:
        request = Request.objects.get(ext_id=ext_id)
        request.completed = True
        request.save()
    except Request.DoesNotExist:
        status, reason = 'ERROR', 'PETICION_ID does not exist'
    if status == 'OK': 
        # We get the instance associated with this request
        try:
            instance = Instance.objects.get(request__ext_id=request.ext_id)
        except Instance.DoesNotExist:
            status, reason = 'ERROR', 'No instance object associated to this PETICION_ID'
        if status == 'OK' and not instance.termination_solicited:
            instance.termination_solicited = True
            instance.state = 'shutting-down'
            instance.save()
            # Terminate the instance in Amazon
            conn = boto.ec2.get_region(request.region.name).connect()
            c=conn.terminate_instances([instance.instance_id])
            a=conn.delete_key_pair(request.ext_id)
            request.termination_date = datetime.now()
            request.save()
            instance.keypair.termination_date = datetime.now()
            instance.keypair.save()
            instance.save()
            # This would be a good place to launch the external notification
            msg = MIMEText(email_stopped)
            msg['Subject'] = "La maquina se ha parado"
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
            status, reason = 'ERROR', 'Stop request already received for this PETICION_ID'
    if status !='OK':
        send_error('stop',reason,'')
    return HttpResponse(simplejson.dumps({'status':status, 'reason':reason}))

def info(request, type, ext_id):
    """
    Instance informacion

    :param request: Request object
    :type request: Request object
    :param type: operation type (info in this case)
    :type type: string
    :param ext_id: External identifier. Used by the service which made the request for tracking purposes
    :type ext_id: integer -10 digits-
    :returns: Instance information or Request status (OK, ERROR, etc)
    :rtype: HttpResponse object (json output)
    """
    status = 'OK'
    reason = ''
    try:
        instance = Instance.objects.get(request__ext_id=ext_id)
        keypair = instance.keypair
        request = instance.request
        ami = instance.ami
        data = serializers.serialize("json", [instance,keypair,request,ami])
    except Instance.DoesNotExist:
        status, reason = 'ERROR', 'PETICION_ID does not exist (perhaps still booting?)'
        data = simplejson.dumps({'status':status, 'reason':reason})
    return HttpResponse(data)

def list(request, type):
    """
    Virtual AMIs list

    :param request: Request object
    :type request: Request object
    :param type: operation type (list in this case)
    :type type: string
    :returns: Virtual AMIs list or Request status (OK, ERROR, etc)
    :rtype: HttpResponse object (json output)
    """
    data = serializers.serialize("json", VirtualAMI.objects.all())
    return HttpResponse(data)

