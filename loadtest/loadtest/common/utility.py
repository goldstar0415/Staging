import random
import math

""" latitude range of world """
def rng_lat():
    return random.uniform(-90, 90)

""" longitude range of world """
def rng_lng():
    return random.uniform(-180, 180)

""" approximate latitude range of usa """
def rng_usa_lat():
    return random.uniform(30, 48)

""" approximate longitude range of usa """
def rng_usa_lng():
    return random.uniform(-124, -71)

""" each event so we can simulate clicking different things """
def rng_event():
    return random.choice(["event", "todo", "food", "shelter"])

""" find distance between two tuples representing latlng """
def dist(a, b):
    return math.sqrt((a[0] - b[0]) ** 2 + (a[1] - b[1]) ** 2)
