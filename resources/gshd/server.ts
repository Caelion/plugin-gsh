/**
* Copyright 2019, Google, Inc.
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*   http://www.apache.org/licenses/LICENSE-2.0
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

// Fakecandy is a fake openpixelcontrol server that:
// - prints led state on standard output.
// - responds to UDP broadcast with device information encoded in CBOR

import * as yargs from "yargs";
const argv = yargs
.usage("Usage: $0 --udp_discovery_port PORT_NUMBER --udp_discovery_packet PACKET_STRING")
.option("udp_discovery_port", {
  describe: "port to listen on for UDP discovery query",
  type: "number",
  demandOption: true,
})
.option("udp_discovery_packet", {
  describe: "packet content to match for UDP discovery query",
  type: "string",
  demandOption: true,
})
.option("pid", {
  describe: "where to write pid file",
  type: "string",
  demandOption: false,
})
.option("loglevel", {
  describe: "log level",
  type: "string",
  demandOption: false,
})
.argv;

import * as fs from 'fs';
import * as cbor from "cbor";
import * as dgram from "dgram";

if(argv.pid){
  fs.writeFile(argv.pid, process.pid, function(err) {
    if(err) {
      process.exit()
    }
  });
}

const socket = dgram.createSocket("udp4");

socket.on("message", (msg, rinfo) => {
  const discoveryPacket = Buffer.from(argv.udp_discovery_packet, "hex");
  if (msg.compare(discoveryPacket) !== 0) {
    if(argv.loglevel && argv.loglevel == 'debug'){
      console.warn("received unknown payload:", msg, "from:", rinfo);
    }
    return;
  }
  if(argv.loglevel && argv.loglevel == 'debug'){
    console.log("received discovery payload:", discoveryPacket, "from:", rinfo);
  }
  const discoveryData = {
    id: 'fake-jeedom-local',
    model: 'fake-jeedom-local',
    hw_rev: '1.0.0',
    fw_rev: '1.0.0'
  };
  const responsePacket = cbor.encode(discoveryData);
  socket.send(responsePacket, rinfo.port, rinfo.address, (error) => {
    if (error !== null) {
      console.error("failed to send ack:", error);
      return;
    }
    if(argv.loglevel && argv.loglevel == 'debug'){
      console.log("sent discovery response:", discoveryData, "to:", rinfo);
    }
  });
});
socket.on("listening", () => {
  if(argv.loglevel && argv.loglevel == 'debug'){
    console.log("discovery listening", socket.address());
  }
}).bind(argv.udp_discovery_port);
