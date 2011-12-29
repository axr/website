Phing Drush Task
--------------------------
A Drush task for Phing[1]. This task enable usage of Drush commands in Phing build scripts.

Phing provides tools for usual tasks for PHP projects (phplint, jslint, VCS checkouts, files copy or merge, packaging, upload, etc.). Integration of Drush in Phing is particularly useful when building and testing Drupal projects in a continuous integration server such as Jenkins[2].
 
Installation and Usage
----------------------------------
To use the drush task in your build file,  it must be made available to Phing so that the buildfile parser is aware a correlating XML element and it's parameters.  This is done by adding a <taskdef> tak to your build file, something like (see Phing documentation[3] for more information on the <taskdef> task).

  <taskdef name="drush" classname="DrushTask" />
  
Base Drush options are mapped to attribute of the Drush task. Parameters are wrapped in elements. Value of a parameter is defined by the text child of the element. Options are mapped to elements with a name attribute. Value of an option can either be in the value attribute of the element or as text child (like params).
drush site-install --yes --locale=uk --site-name =${sitename} expert
  <drush command="site-install" assume="yes"">
    <option name="locale">uk</option>
    <option name="site-name" value="${sitename}" />
    <param>expert</param>
  </drush> 

More sample usages are provided in the tamplte build script at http://reload.github.com/phing-drupal-template/
  
[1] http://www.phing.info/
[2] http://jenkins-ci.org/
[3] http://www.phing.info/docs/guide/stable/chapters/appendixes/AppendixB-CoreTasks.html#TaskdefTask
