#!/usr/bin/env python
# Только Python 3.x

# Импорты из Python
import asyncio, time, sqlite3
from xml.sax.handler import ContentHandler
from xml.sax import make_parser

def console_message(message, color = 'default'):
	if color == 'red':
		print("%s%s%s" % ('\033[91m', message, '\033[0m'))
	elif color == 'green':
		print("%s%s%s" % ('\033[92m', message, '\033[0m'))
	elif color == 'blue':
		print("%s%s%s" % ('\033[94m', message, '\033[0m'))
	else:
		print(message)

# XML-станза
class Stanza:
	def __init__(self, stream, tag):
		self._tag = tag

	def tag(self):
		return self._tag

	def to(self):
		return self._tag.getAttribute('to')

	def __repr__(self):
		return 'TODO'

# XML-элемент
class Tag:
	def __init__(self, name, attributes):
		self._name = name
		self._attributes = attributes
		self._children = []
		self._cdata = []

	def insertChildElement(self, element):
		self._children.append(element)

	def insertCharacterData(self, cdata):
		self._cdata.append(cdata)

	def name(self):
		return self._name

	def attributes(self):
		return self._attributes

	def getAttribute(self, name, default_value = None):
		try:
			return self._attributes[name]
		except:
			return default_value

# XML-поток
class XmlStream(asyncio.Protocol, ContentHandler):
	def connection_made(self, transport):
		self._peername = transport.get_extra_info('peername')
		console_message('Connection from {}'.format(self._peername), 'green')
		self.transport = transport
		self._type = None

	def data_received(self, data):
		try:
			self._parser.feed(data.decode())
		except Exception as e:
			self.close('XML parse error, closing stream')

	def close(self, message):
		console_message(message, 'red')
		try:
			self.transport.write(b'</stream>')
		except:
			pass
		self.transport.close()

	def startElement(self, name, attrs):
		tag = Tag(name, attrs)
		self._stack.append(tag)
		if len(self._stack) == 1:
			return self.handleStreamStart(tag)

	def endElement(self, name):
		if len(self._stack) == 1:
			return self.handleStreamEnd()
		else:
			try:
				top = self._stack.pop()
				if top.name() != name:
					self.close("Found closing tag for %s, but %s was expected" % (name, top.name()))

				if len(self._stack) == 1:
					stanza = Stanza(self, top)
					self.handleStanza(stanza)
				else:
					stack[-1].insertChildElement(top)
			except IndexError as e:
				self.close("Found a closing tag, but no tag is currently open!")
			except Exception as e:
				console_message(e, 'red')
				self.close("Unknown error")

	def characters(self, data):
		self._stack[-1].insertCharacterData(data);

	def handleStreamStart(self):
		raise NotImplementedError

	def handleStreamEnd(self):
		raise NotImplementedError

	def handleStanza(self, stanza: Stanza):
		raise NotImplementedError

	def __init__(self):
		self._parser = make_parser(['IncrementalParser'])
		self._parser.setContentHandler(self)
		self._stack = []

# Агентский поток
class ControllerAgentStream(XmlStream):
	def handleStreamStart(self, tag: Tag):
		if tag.name() != 'stream':
			self.close('Root element should be <stream> with proper type attribute!')
		else:
			self._type = tag.getAttribute('type')
			console_message('New %s stream' % self._type, 'green')

			if self._type == 'module':
				self.onModuleConnect(tag.attributes())
			elif self._type == 'host':
				self.onHostConnect(tag.attributes())
			else:
				self.close('Only host and module streams are currently supported!')

	def handleStreamEnd(self):
		self.transport.close()

	def handleStanza(self, stanza):
		console_message('Got a stanza: %s' % repr(stanza))
		recipient = stanza.to()
		sender = "%s:%s" % (self._type, self._name)

	def onModuleConnect(self, attributes):
		self._name = tag.getAttribute('module_name')
		self._key = tag.getAttribute('module_key')

	def onHostConnect(self, attributes):
		self._name = tag.getAttribute('host_name')
		self._key = tag.getAttribute('host_key')

# Контроллер
class Controller:
	def __init__(self, config):
		self.loop = asyncio.get_event_loop()
		coroutine = self.loop.create_server(ControllerAgentStream, '0.0.0.0', config.PORT)
		self.server = self.loop.run_until_complete(coroutine)
		self._config = config

	def handle_alarm(self):
		global time
		self.loop.call_later(0.1, self.handle_alarm)

	def report_status(self):
		console_message('Controller running', 'green')

	def run(self):
		self.loop.call_later(0.1, self.handle_alarm)
		self.loop.call_later(0.1, self.report_status)
		try:
			self.loop.run_forever()
		except KeyboardInterrupt:
			console_message("Exitting on demand", 'green')

		self.server.close()
		self.loop.run_until_complete(self.server.wait_closed())
		self.loop.close()

console_message('Starting controller', 'blue')
console_message('Loading configuration file', 'blue')

try:
	import config
except:
	console_message("Failed to load config.py", 'red')
	exit(-1)

console_message('Opening the database', 'blue')

db = sqlite3.connect(config.DB)

console_message('Initializing daemon', 'blue')

controller = Controller(config)
controller.run()

db.close()
