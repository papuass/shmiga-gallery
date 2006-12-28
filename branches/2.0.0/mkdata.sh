#!/bin/sh

echo '<?xml version="1.0" encoding="UTF-8"?>' > data.xml
echo '<gallery>' >> data.xml
echo -e "\t<caption>Galerija</caption>" >> data.xml

echo -e "\t<images>" >> data.xml
for f in `ls *.jpg`
do
 echo -e "\t\t<image name=\""${f}"\" vip=\"*\"><![CDATA[]]></image>" >> data.xml
done
echo -e "\t</images>" >> data.xml

echo "\t<documents>" >> data.xml
echo '<!-- <document name="XXX" vip="*"><![CDATA[XXX]]></document>-->' >> data.xml
echo "\t</documents>" >> data.xml

echo "\t<galleries>" >> data.xml
echo '<!-- <gallery name="XXX" vip="*">XXX</gallery>' >> data.xml
echo "\t</galleries>" >> data.xml

echo '</gallery>' >> data.xml
