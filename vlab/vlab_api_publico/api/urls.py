from django.conf.urls.defaults import *

urlpatterns = patterns('',
    (r'^api/(?P<type>launched)/(?P<ext_id>\d{10})/$', 'vlab_api_publico.api.views.launched'),
    (r'^api/(?P<type>stopped)/(?P<ext_id>\d{10})/$', 'vlab_api_publico.api.views.stopped'),
    (r'^api/(?P<type>imalive)/(?P<ext_id>\d{10})/$', 'vlab_api_publico.api.views.imalive'),
)
