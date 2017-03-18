## install mitmproxy
sudo apt-get install python3.5 python3.5-dev libffi-dev libssl-dev
cd mitmproxy
virtualenv -p /usr/bin/python3.5 venv
source venv/bin/activate
pip install -r requirements.txt

## run mitmproxy
cd mitproxy
source venv/bin/activate
mitmproxy --anticache --host

ensure your proxy settings are correct


# pip install locustio
