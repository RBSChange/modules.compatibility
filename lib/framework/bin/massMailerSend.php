<?php
// This task is executed by emailing module for mass mail sending and has not to be cronned
MassMailer::getInstance()->sendMessagePaths($arguments);