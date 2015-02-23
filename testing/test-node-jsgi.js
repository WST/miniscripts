#!/usr/bin/node

require("jsgi-node").start(function(request) {
	return {
		status: 200,
		headers: {},
		body: ["Hello World!"]
	};
});

