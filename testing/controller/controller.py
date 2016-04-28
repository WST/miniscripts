#!/usr/bin/env python

import asyncio, time
from xml.sax.handler import ContentHandler
from xml.sax import make_parser

# XML-поток
class XmlStream(asyncio.Protocol, ContentHandler):
	def data_received(self, data):
		xml = data.decode()
		try:
			self.parser.feed(xml)
		except Exception as e:
			print(repr(e))
			self.transport.write(b'</stream>')
			self.transport.close()

	def startElement(self, name, attrs):
		pass

	def endElement(self, name):
		pass

	def characters(self, data):
		pass

	def __init__(self):
		self.parser = make_parser(['IncrementalParser'])
		self.parser.setContentHandler(self)

# Агентский поток
class ControllerAgentStream(XmlStream):
	def connection_made(self, transport):
		peername = transport.get_extra_info('peername')
		print('Connection from {}'.format(peername))
		self.transport = transport

# Контроллер
class Controller:
	def __init__(self):
		self.loop = asyncio.get_event_loop()
		coroutine = self.loop.create_server(ControllerAgentStream, '127.0.0.1', 8888)
		self.server = self.loop.run_until_complete(coroutine)

	def handle_alarm(self):
		global time
		print("ALARM EVENT %.2f" % time.time())
		self.loop.call_later(0.1, self.handle_alarm)

	def run(self):
		self.loop.call_later(0.1, self.handle_alarm)
		try:
			self.loop.run_forever()
		except KeyboardInterrupt:
			pass

		self.server.close()
		self.loop.run_until_complete(self.server.wait_closed())
		self.loop.close()

controller = Controller()
controller.run()
