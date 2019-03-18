/*
1. Create a folder named "email" within "tinymce/plugins".
2. Create a file called "plugin.min.js" within the folder.
2. Paste the below code inside "tinymce/plugins/email/plugin.min.js"
3. Extend your tiny.init like:
    tinymce.init({
        plugins: "email",
        toolbar: "email"
 });
*/

tinymce.PluginManager.add('email', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('email', {
        text: 'E-Mail',
        icon: false,
        onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'E-Mail Address',
                body: [
                    {type: 'textbox', name: 'title', label: 'E-Mail'}
                ],
                onsubmit: function(e) {
                    // Insert content when the window form is submitted
                    editor.insertContent('<a href="mailto:' + e.data.title + '">' + e.data.title + '</a>');
                }
            });
        }
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('email', {
        text: 'E-Mail',
        context: 'tools',
        onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'E-Mail Address',
                body: [
                    {type: 'textbox', name: 'title', label: 'E-Mail'}
                ],
                onsubmit: function(e) {
                    // Insert content when the window form is submitted
                    editor.insertContent('<a href="mailto:' + e.data.title + '">' + e.data.title + '</a>');
                }
            });
        }
    });
});