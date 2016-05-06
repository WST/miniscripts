#!/usr/bin/env python
# Только Python 3.x

# Импорты из Python
import asyncio, time
from xml.sax.handler import ContentHandler
from xml.sax import make_parser

# XML-элемент
class Tag:
	def __init__(self, name, attributes):
		self._name = name
		self._attributes = attributes
		self._children = []

	def insertChildElement(self, element):
		self._children.append(element)

	def name(self):
		return self._name

# XML-поток
class XmlStream(asyncio.Protocol, ContentHandler):
	def data_received(self, data):
		xml = data.decode()
		try:
			self._parser.feed(xml)
		except Exception as e:
			print(repr(e))
			self.close('XML error, closing stream')

	def close(self, message):
		print(message)
		self.transport.write(b'</stream>')
		self.transport.close()

	def startElement(self, name, attrs):
		tag = Tag(name, attrs)
		if len(self._stack) == 0:
			return self.handleStreamStart(tag)
		else:
			self._stack.append(tag)

	def endElement(self, name):
		if len(self._stack) == 1:
			return self.handleStreamEnd()
		else:
			try:
				top = self._stack.pop()
				if top.name() != name:
					self.close("Found closing tag for %s, but %s was expected" % (name, top.name()))

				if len(self.stack) == 1:
					stanza = Stanza(self, top)
					self.handleStanza(stanza)
				else:
					stack[-1].insertChildElement(top)
			except IndexError as e:
				self.close("Found a closing tag, but no tag is currently open!")
			except:
				self.close()

	def characters(self, data):
		self._stack[-1].insertCharacterData(data);

	def handleStreamStart(self, tag: Tag):
		raise NotImplementedError

	def handleStreamEnd(self):
		raise NotImplementedError

	def __init__(self):
		self._parser = make_parser(['IncrementalParser'])
		self._parser.setContentHandler(self)
		self._stack = []

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
		#print("ALARM EVENT %.2f" % time.time())
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
