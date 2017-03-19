""" pretending to be a browser """
def get_headers():
    return {
        'User-Agent': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0',
        'Accept': 'application/json, text/plain, */*',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate',
        'Connection': 'keep-alive',
    }

""" minimum time in milliseconds, that a simulated user will wait between executing each task """
def get_min_wait():
    return 5000


""" maximum time in milliseconds, that a simulated user will wait between executing each task """
def get_max_wait():
    return 9000
