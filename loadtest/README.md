# Setup

apt-get install python3.5 python3-venv python-pip python-dev libffi-dev libssl-dev libxml2-dev libxslt1-dev libjpeg8-dev zlib1g-dev
python3.5 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Running

Run this to load the virtual environment
source venv/bin/activate


To run you can either use browser interface like so:

locust -f loadtest/locustfiles/FILE  --host=http://zoomtivity-back.dev

Or CLI

locust -f loadtest/locustfiles/FILE --host=http://zoomtivity-back.dev --no-web -c 1000 -r 10

Clients to be 1000 and rate 10, simulates good burst but feel free to play around with these numbers
