# -*- coding: utf8 -*-

from vlab_admin.api.models import *
from django.contrib import admin

class RegionAdmin(admin.ModelAdmin):
    list_display = ['name','priority']
    list_filter = ['priority']
    search_fields = ['name']

class KeypairAdmin(admin.ModelAdmin):
    list_display = ['name','fingerprint','creation_date','termination_date','region']
    list_filter = ['region']
    search_fields = ['name','region__name']

class InstanceAdmin(admin.ModelAdmin):
    list_display = ['instance_id','state','public_dns_name','type','creation_date','estimated_termination_date','real_termination_date','termination_solicited','keypair','ami','request','last_imalive_date']
    list_filter = ['state','type','ami']
    search_fields = ['instance_id','public_dns_name','keypair','ami']

class VirtualAMIAdmin(admin.ModelAdmin):
    list_display = ['name','description']
    list_filter = ['name']
    search_fields = ['name','description']

class AMIAdmin(admin.ModelAdmin):
    list_display = ['name','region','virtual_ami','ami_parameters']
    list_filter = ['region','virtual_ami']
    search_fields = ['name','virtual_ami']

class AmiParametersAdmin(admin.ModelAdmin):
    list_display = ['name','ari','aki','date','active']
    list_filter = ['active','date']
    search_fields = ['active','date']

class RequestAdmin(admin.ModelAdmin):
    list_display = ['ext_id','creation_date','termination_date','request_type','callback_url','sir_id','completed','region','virtual_ami','email','launch_error_notified']
    list_filter = ['region','virtual_ami','completed','request_type']
    search_fields = ['ext_id','virtual_ami']

admin.site.register(Region, RegionAdmin)
admin.site.register(Keypair, KeypairAdmin)
admin.site.register(Instance, InstanceAdmin)
admin.site.register(VirtualAMI, VirtualAMIAdmin)
admin.site.register(AMI, AMIAdmin)
admin.site.register(Request, RequestAdmin)
admin.site.register(AmiParameters, AmiParametersAdmin)
