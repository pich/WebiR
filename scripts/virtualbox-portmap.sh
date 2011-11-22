#!/bin/bash

VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/Protocol" TCP
VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/GuestPort" 20088
VBoxManage setextradata "WebiR" "VBoxInternal/Devices/pcnet/0/LUN#0/Config/zendwebserver/HostPort" 20088
