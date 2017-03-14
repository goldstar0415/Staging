FROM mhart/alpine-node:6

COPY package.json /opt/websocket_server/
WORKDIR /opt/websocket_server
RUN npm install

COPY socket.js /opt/websocket_server/

EXPOSE 3000
CMD ["npm", "run", "start"] # todo: add a 'start' cmd
