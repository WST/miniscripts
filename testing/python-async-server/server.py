#!/usr/bin/env python

import asyncore, threading

# Создадим мьютекс
mutex = threading.Lock()

# Модуль asyncore на данный момент считается устаревшим
# https://docs.python.org/3.5/library/asyncore.html
# Рекомендуется использовать asyncio
# Но с ним нужно ещё разобраться.

# Класс dispatcher_with_send отличается от обычного dispatcher тем, что в нём
# инкапсулирована буферизация отправляемых клиенту данных, тем самым гарантируется,
# что вызов send не вызовет блокировку. Данные будут отправлены, когда получится.
class EchoHandler(asyncore.dispatcher_with_send):
	def handle_read(self):
		data = self.recv(8192)
		if data:
			self.send(data)

		self.close()

# Основной класс сервера
class EchoServer(asyncore.dispatcher):
	def __init__(self, host, port):
		asyncore.dispatcher.__init__(self)
		self.create_socket()
		self.set_reuse_addr()
		self.bind((host, port))
		self.listen(5)

	def handle_accepted(self, sock, addr):
		mutex.acquire()
		print('Incoming connection from %s' % repr(addr))
		handler = EchoHandler(sock)
		mutex.release()

server = EchoServer('localhost', 8888)

worker_1 = threading.Thread(target = asyncore.loop)
worker_2 = threading.Thread(target = asyncore.loop)

worker_1.start()
worker_2.start()

worker_1.join()
worker_2.join()

