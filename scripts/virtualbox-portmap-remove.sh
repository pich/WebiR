#!/bin/bash

VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/Protocol"
VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/GuestPort"
VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/HostPort"
