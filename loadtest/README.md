# Setup

virtualenv -p /usr/bin/python3.5 venv
source venv/bin/activate
pip install -r requirements.txt

# Running

To run you can either use browser interface like so:

locust -f loadtest/locustfiles/FILE  --host=http://zoomtivity-back.dev

Or CLI

locust -f loadtest/locustfiles/FILE --host=http://zoomtivity-back.dev --no-web -c 1000 -r 10

Clients to be 1000 and rate 10, simulates good burst but feel free to play around with these numbers
