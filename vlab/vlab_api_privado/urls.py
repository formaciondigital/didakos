from django.conf.urls.defaults import *

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    (r'^vlab/', include('vlab_api_privado.api.urls')),
)
