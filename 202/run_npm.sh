#!/bin/bash

#
# Since 2007 PayPal
#
# NOTICE OF LICENSE
#
# This source file is subject to the Academic Free License (AFL 3.0)
# that is bundled with this package in the file LICENSE.txt.
# It is also available through the world-wide-web at this URL:
# http://opensource.org/licenses/afl-3.0.php
# If you did not receive a copy of the license and are unable to
# obtain it through the world-wide-web, please send an email
# to license@prestashop.com so we can send you a copy immediately.
#
# DISCLAIMER
#
# Do not edit or add to this file if you wish to upgrade PrestaShop to newer
#  versions in the future. If you wish to customize PrestaShop for your
#  needs please refer to http://www.prestashop.com for more information.
#
#  @author Since 2007 PayPal
#  @author 202 ecommerce <tech@202-ecommerce.com>
#  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
#  @copyright PayPal
#
#

export NVM_DIR="$HOME/.nvm" # set local path to NVM
. ~/.nvm/nvm.sh             # add NVM into the Shell session
nvm install 14.17.3         # install version (done only one time)
nvm use 14.17.3             # use choosed version
npm install
npm rebuild node-sass       # could be needed once if version changed
npm run build
rm views/js/*.map
rm views/css/*.map
