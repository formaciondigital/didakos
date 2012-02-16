# -*- coding: utf8 -*-

import boto
from django.db import models
from datetime import datetime


class Region(models.Model):
    name = models.CharField('Name', max_length=20, unique=True)
    priority = models.IntegerField('Priority', null=True, blank=True)

    def __unicode__(self):
        return self.name


class Keypair(models.Model):
    name = models.CharField('Name', max_length=50, unique=True)
    fingerprint = models.CharField('Fingerprint', max_length=100, unique=True)
    key = models.TextField('unencrypted PEM encoded RSA private key', null=True, blank=True)
    creation_date = models.DateTimeField('Creation date', default=datetime.now)
    termination_date = models.DateTimeField('Termination date', null=True, blank=True)
    region = models.ForeignKey(Region)

    def __unicode__(self):
        return self.name


class VirtualAMI(models.Model):
    name = models.CharField('Name', max_length=50, unique=True)
    description = models.CharField('Description', max_length=100, null=True, blank=True)

    def __unicode__(self):
        return self.name

class AmiParameters(models.Model):
    name = models.CharField('Name', max_length=50)
    ari = models.CharField('Ari', max_length=50)
    aki = models.CharField('Aki', max_length=50)
    date = models.DateTimeField('Date', default=datetime.now)
    active = models.BooleanField('Active', default=False)

    def __unicode__(self):
        status='N'
        if self.active==1:
            status='S'
        return " %s activo: %s"% (self.name,status)
       
class AMI(models.Model):
    name = models.CharField('Name', max_length=50, unique=True)
    region = models.ForeignKey(Region)
    virtual_ami = models.ForeignKey(VirtualAMI)
    ami_parameters = models.ForeignKey(AmiParameters)


    def __unicode__(self):
        return self.name


class Request(models.Model):
    ext_id = models.CharField('Name', max_length=20, unique=True)
    request_type = models.CharField(max_length=10)
    creation_date = models.DateTimeField('Creation date', default=datetime.now)
    termination_date = models.DateTimeField('Termination date', blank=True, null=True)
    time = models.IntegerField('Machine life time')
    callback_url = models.CharField('Callback URL', max_length=100, blank=True, null=True)
    sir_id = models.CharField('Spot Instance Request ID', max_length=20, blank=True, null=True)
    completed = models.BooleanField('Any notification received for this request?', default=False)
    region = models.ForeignKey(Region)
    virtual_ami = models.ForeignKey(VirtualAMI)
    email = models.CharField('Email', max_length=100, blank=True, null=True)
    launch_error_notified = models.CharField('Notificacion launch error',max_length=1,default='n')
    
    def __unicode__(self):
        return self.ext_id


class Instance(models.Model):
    instance_id = models.CharField('Instance ID', max_length=20)
    type = models.CharField('Type', max_length=20)
    creation_date = models.DateTimeField('Creation date', default=datetime.now)
    last_imalive_date = models.DateTimeField('Last Imalive date', default=datetime.now)
    estimated_termination_date = models.DateTimeField('Estimated termination date', blank=True, null=True)
    real_termination_date = models.DateTimeField('Real termination date', blank=True, null=True)
    public_dns_name = models.CharField('Public DNS', max_length=100)
    state = models.CharField('State', max_length=20, blank=True)
    five_minutes_notified = models.CharField('Notificacion five minutes',max_length=1,default='n')
    stop_error_notified = models.CharField('Notificacion error de parada',max_length=1,default='n')
    termination_solicited = models.BooleanField('Termination request sent?', default=False)
    keypair = models.ForeignKey(Keypair)
    ami = models.ForeignKey(AMI)
    request = models.ForeignKey(Request)
    
    def __unicode__(self):
        return self.instance_id
