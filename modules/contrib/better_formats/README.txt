This very basic documentation for during development.
Better docs will be generated closer to a full release.


The only items currently implemented in the D8 version of Better Formats are:

1. Display options: When BF is enabled you will have permissions at
   admin/people/permissions to control per role display of:
   1. format tips
   2. format tips link
   3. format selection for [entity]

   #3 is actually several permissions. There is one for each entity in your
   site.

2. Simple field level default format.
   This allows you set a field level default format using the standard "Default
   Value" setting of a field. This is only possibly normally if you enter
   something in the text field for the field api to save the format too. BF
   gives you the ability to set the format WITHOUT having to set a value in the
   field.

   1. At admin/config/content/formats/settings enable "Use field default"
      option.
   2. Create a field on one of your entities with one of the following types: 
      Text, Long Text, Long text with summary.
   3. Save the field.
   4. Now go back and edit the field you just saved. This is required because of
      how the field default value option works.
   5. You will now see a "Text format" dropdown below your field in the
      "Default Value" area. Set the default format in the dropdown.
   6. Save the field. Default will now be used on all new content forms for that
      field.

3. Allowed format selection for each text field.

4. Ordering of formats in Formats select box for each text field.
