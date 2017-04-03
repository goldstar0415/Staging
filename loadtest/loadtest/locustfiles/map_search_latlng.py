import sys
sys.path.append('/var/www/zoomtivity/loadtest')

from locust import HttpLocust, TaskSet, task
import loadtest.common.utility as util
import loadtest.common.shared as shared
import random


class UserBehavior(TaskSet):

    def on_start(self):
        """ on_start is called when a Locust start before any task is scheduled """
        pass


    @task(1)
    def map_selection_radius(self):
        url = self.locust.host + '/map/search'
        
        params = {
            'query': util.rng_alphanumeric_str(random.randint(3, 20)),
            'lat': str(util.rng_usa_lat()),
            'lng': str(util.rng_usa_lng()),
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

