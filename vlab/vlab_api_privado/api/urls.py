from django.conf.urls.defaults import *

urlpatterns = patterns('',
    (r'^api/(?P<type>new)/(?P<ext_id>\d{10})/(?P<time>\d{3})/(?P<virtual_ami>\d{10})/(?P<email>[a-z0-9_.-]+@[a-z0-9_.-]+.[a-z]+)/(?P<zone>[a-zA-Z0-9_.-]+)/$', 'vlab_api_privado.api.views.new'),
    (r'^api/(?P<type>new)/(?P<ext_id>\d{10})/(?P<time>\d{3})/(?P<virtual_ami>\d{10})/(?P<email>[a-z0-9_.-]+@[a-z0-9_.-]+.[a-z]+)/$', 'vlab_api_privado.api.views.new'),
    (r'^api/(?P<type>stop)/(?P<ext_id>\d{10})/$', 'vlab_api_privado.api.views.stop'),
    (r'^api/(?P<type>info)/(?P<ext_id>\d{10})/$', 'vlab_api_privado.api.views.info'),
    (r'^api/(?P<type>list)/$', 'vlab_api_privado.api.views.list'),
)
