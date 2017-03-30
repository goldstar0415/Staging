import sys
sys.path.append('/var/www/zoomtivity/loadtest')

from locust import HttpLocust, TaskSet, task
import loadtest.common.utility as util
import loadtest.common.shared as shared
import random
import noise
import math


class UserBehavior(TaskSet):

    def on_start(self):
        """ on_start is called when a Locust start before any task is scheduled """
        pass

    # todo: investigate 1% error on this
    @task(1)
    def map_selection_lasso(self):
        url = self.locust.host + '/map/selection/lasso'
        
        # for lasso we will extrude a circle from a point placed upon the usa
        point  = (util.rng_usa_lat(), util.rng_usa_lng())

        # size of shape
        latsize = random.uniform(1, 5)
        lngsize = random.uniform(1, 10)

        # how many vertices 
        detail = 3 
        
        # spacing on ellipsoid between each vertex
        spacing = (math.pi * 2) / detail

        # mag or magnitude refers to the amount our noise value should apply
        # should it be strong (large hills) or weak (small bumps)
        mag = random.uniform(1, 7)

        # mult refers to how much our positions should be taken into account.
        # a low value will result in a smooth surface, a large value equates to a very varying surface
        mult = random.uniform(0.25, 0.5)


        vertices = []

        for i in range(0, detail):
            angle = spacing * i
            ax = math.cos(angle)
            ay = math.sin(angle)

            axm = ax * mult
            aym = ay * mult

            lat = point[0] + (ax * latsize * ((1.1 + noise.snoise2(axm, aym)) * mag))
            lng = point[1] + (ay * lngsize * ((1.1 + noise.snoise2(axm, aym)) * mag))

            vertices.append(str(lat) + "," + str(lng))

        params = {
            'filter[rating]': '0',
            'filter[is_approved]': 'true',
            'filter[type]': util.rng_event(),
            'vertices[]': vertices
        }

        self.response = self.client.request(
            method='GET',
            url=url,
            headers=shared.get_headers(),
            params=params,
        )

class WebsiteUser(HttpLocust):
    task_set = UserBehavior
    min_wait = shared.get_min_wait()
    max_wait = shared.get_max_wait()

