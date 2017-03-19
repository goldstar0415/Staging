from locust import HttpLocust, TaskSet, task
import loadtest.common.utility as util
import loadtest.common.shared as shared
import math
import random


class UserBehavior(TaskSet):

    def on_start(self):
        """ on_start is called when a Locust start before any task is scheduled """
        pass

    """ this is used so that the path generated isnt just a straight line
        and more closely looks like results from map query """
    def random_offset(self, m):
        return random.uniform(-m, m)

    @task(1)
    def map_selection_path(self):
        url = self.locust.host + '/map/selection/path'

        # where we begin and end our journey
        # todo: allow for more than 2 paths to be added
        start_coord = (util.rng_usa_lat(), util.rng_usa_lng())
        end_coord   = (util.rng_usa_lat(), util.rng_usa_lng())

        # point our way from start to end
        angle = math.atan2(start_coord[1] - end_coord[1], start_coord[0] - end_coord[0])

        # how many times to segment line
        segment_amount = 100

        # the distance between each segment
        segment_distance = util.dist(start_coord, end_coord) / segment_amount

        vertices = []
        for i in range(0, segment_amount):
            vertices.append(
                str(start_coord[0] + (i * segment_distance * math.cos(angle)) + self.random_offset(segment_distance))
                + "," + 
                str(start_coord[1] + (i * segment_distance * math.sin(angle)) + self.random_offset(segment_distance))
            )

        params = {
            'filter[rating]': '0',
            'filter[is_approved]': 'true',
            'filter[type]': util.rng_event(),
            'buffer': '100000',
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
