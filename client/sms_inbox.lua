#!/usr/bin/env lua

-- requires base64.lua from https://github.com/toastdriven/lua-base64
-- requires JSON.lua from http://regex.info/blog/lua/json
require("base64")
JSON = (loadfile "JSON.lua")()

password = "super-hero-password"
messages_url = "http://your.domain.tld/api/v1/messages.php"

local my_id          = arg[1]
local caller_id      = arg[2]
local timestamp      = arg[3]
local content_base64 = arg[4]
local content        = from_base64(content_base64)

local date           = os.date("*t", timestamp)
local date_time      = string.format("%04d-%02d-%02d %02d:%02d:%02d", date.year, date.month, date.day, date.hour, date.min, date.sec)

local data = {
    caller_id = caller_id,
    content   = content,
    date_time = date_time,
    my_id     = my_id,
}

os.execute("echo '" .. date_time .. " - " .. "dongle0" .. " - " .. caller_id .. ": " .. data.content .. "' >> /var/log/asterisk/sms.txt")

-- POST data to server
local json = JSON:encode(data)

local handle = io.popen("echo '" .. json .. "' | openssl enc -aes-256-cbc -a -k " .. password)
local encrypted = handle:read("*a")
handle:close()

encrypted = encrypted:gsub("%s+", "")

os.execute("curl --data-urlencode 'encrypted=" .. encrypted .. "' " .. messages_url)
