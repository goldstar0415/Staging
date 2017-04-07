FROM mhart/alpine-node:6

COPY package.json /opt/websocket_server/
COPY entrypoint.sh /opt/websocket_server/
COPY socket.js /opt/websocket_server/

WORKDIR /opt/websocket_server

RUN npm install && chmod +x entrypoint.sh

EXPOSE 3000

CMD ["./entrypoint.sh"]
