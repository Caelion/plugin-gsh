touch /tmp/dependancy_gsh_in_progress
echo 0 > /tmp/dependancy_gsh_in_progress
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
sudo apt update
sudo apt-get -y install lsb-release
release=$( lsb_release -c -s )
echo 40 > ${PROGRESS_FILE}
if [ -x /usr/bin/nodejs ]; then
  actual=`nodejs -v | awk -F v '{ print $2 }' | awk -F . '{ print $1 }'`;
  echo "Version actuelle : ${actual}"
else
  actual=0;
  echo "Nodejs non installé"
fi
if [ $actual -gt 10 ]; then
  echo "Ok, version suffisante";
else
  echo "KO, version obsolète à upgrader";
  echo "Suppression du Nodejs existant et installation du paquet recommandé"
  sudo apt-get -y --purge autoremove nodejs npm
  arch=`arch`;
  echo 30 > $LOG
  if [ $arch == "armv6l" ]; then
    echo "Raspberry 1 détecté, utilisation du paquet pour armv6"
    sudo rm /etc/apt/sources.list.d/nodesource.list
    wget http://node-arm.herokuapp.com/node_latest_armhf.deb
    sudo dpkg -i node_latest_armhf.deb
    sudo ln -s /usr/local/bin/node /usr/local/bin/nodejs
    rm node_latest_armhf.deb
  else
    echo "Utilisation du dépot officiel"
    curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
    sudo apt-get install -y nodejs
  fi
  new=`nodejs -v`;
  echo "Version actuelle : ${new}"
fi
echo 45 > ${PROGRESS_FILE}
sudo apt-get -y install npm
cd ${BASEDIR}/gshd
sudo npm install
echo 95 > /tmp/dependancy_gsh_in_progress

echo 100 > /tmp/dependancy_gsh_in_progress
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm /tmp/dependancy_gsh_in_progress
