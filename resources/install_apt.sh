touch /tmp/dependancy_gsh_in_progress
echo 0 > /tmp/dependancy_gsh_in_progress
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
sudo apt update
cd ${BASEDIR}/gshd
npm install
echo 95 > /tmp/dependancy_gsh_in_progress

echo 100 > /tmp/dependancy_gsh_in_progress
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm /tmp/dependancy_gsh_in_progress
