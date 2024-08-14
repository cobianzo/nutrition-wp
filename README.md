# TODO:

✅ finish the block for aliment in the backend
✅ insert realistic data: All aliments for the example pdf. Create the diet.
✅ Start using the default template for every term. -
✅create pattern day-of-week, with an empty aliment.
sync all personal fields: first name, telephone, Client <=> WP User.
Create frontend for the Dieta. And permissions to be visible only for editor-admin- and owner
✅ create restrictions of use of blocks for 'Editor' user
Create a link in the sidebar of Dieta to go back to the client Edit.
Show link to the diet(s) of a client from the client dashboard.

# nutrition-wp

Wordpress project, with child theme, to create a nutritionist website

1. Install Plugins
   ACF PRO
   Members
   Post Duplicator
   Loco Translator
   Create Block Theme

   Optional: Admin columns, ASE (https://www.youtube.com/watch?v=Vt9Lgp_AtVw), Duplicator

2. Create Custom Post types
   Dieta
   Cliente
   Alimento

   Dieta-category (stitichezza/generica/..)

   Insert setup to ass new styles with css to the editor pages.

3. Create -
   - Sync creation/deletion of 'Cliente' with a 'cliente' role editor.
   - Create pattern for logorrea, etc in html templates.
     - Create a field in Dieta Category to a pattern for the default new dieta.
   - Create a fully custom control panel inside Client. (client Dashboard)
   - Create a custom block for alimenti inside of a dieta.

Development of all the frontend: use of the right parent theme, definition of styles using theme.json, create the right templates for the right pages on frontend. (restrict access only to logged in users?)

By the End:

- Cambiare twentytwentyfour per un'altro block theme.
- Dashboard for Cliente login - remove access to anything except the profile.php. Remove even the sidebar.
- Move ACF into programmatic
- Internazionalization With Loco Translator
- Set up permissions. Access to Editor to certain blocks.
- Use AAM plugin to show/hide content and add redirects.

# Testing cases:

- Sync user / client CPT
-
