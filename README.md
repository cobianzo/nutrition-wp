# NEXT:

- Aprender Wordpress, Full site editing. Maquetar con bloques nativos.
- Uso de Loco Translate for translating. Explicar.
- Tener ya la lsita entera de productos.
- Crear la plantilla definitiva de Programma alimentare, con block Piatto
- Nice to have: sort clients by last visit. Show last visit of client, saved as meta.
- Pasar CPT ya a codigo.
- Create the CPT with template and locked groups, especially for programme:
  - Generica, generic info
  - colazione
  - Spuntino facoltativo
    - Pranzo
  - Spuntino facoltativo
  - Cena
  - Spezie
  - Tisane

# Case Study:

1. Arriva nuovo cliente: Creare nuovo paziente. Salvare.
2. Confermare che arriva mail all'utente. Lui dovrebbe poter aggiornare il profilo e password.
3. Il profile Paziente - WP Utente dovrebbe sincronizzare.
4. Creare un nuovo programma alimentare per il cliente, partendo da una template.

- Bisogna aver create diversi template per Programma alimentare: /wp-admin/site-editor.php?categoryId=programma-alimentare&postType=wp_block
- Il pattern del programma alimentare deve aver la categoria Programma Alimentare.
- Il pattern deve contenere blocchi di tipo Piatto, associati ad un alimento.

5. Creare una nuova dieta. Si puo creare basato in una templae, o basato negli alimenti del programma.

# TODO:

Include relevant data for the client when visitng the homepage. Last visit... etc.
✅ finish the block for aliment in the backend
✅ insert realistic data: All aliments for the example pdf. Create the diet.
✅ Start using the default template for every term. -
✅ create pattern day-of-week, with an empty aliment.
✅ sync all personal fields: first name, telephone, Client <=> WP User.
✅ Create frontend for the Dieta.
And permissions to be visible only for editor-admin- and owner
✅ Usar un buen tema de bloques que se vea bien.
✅ create restrictions of use of blocks for 'Editor' user
✅ Create a link in the sidebar of Dieta to go back to the client Edit. (in metabox)
✅ Show link to the diet(s) of a client from the client dashboard. Allow to delete the diet from there.
whitelist the patterns with category 'diet' in the Allowed blocks
Improve the look of an Aliment Block. Front and backend.
Start translations.

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
   Programma

   Dieta-category (stitichezza/generica/..)

   Insert setup to ass new styles with css to the editor pages.

3. Create -
   - Sync creation/deletion of 'Cliente' with a 'cliente' role editor.
   - Create pattern for logorrea, etc in html templates.
     - Create a field in Dieta Category to a pattern for the default new dieta.
   - Create a fully custom control panel inside Client. (client Dashboard). Allows to create a new diet,
   - Create a custom block for alimenti inside of a dieta.
   - Create a custom block for piatti inside a programme

Development of all the frontend: use of the right parent theme, definition of styles using theme.json, create the right templates for the right pages on frontend. (restrict access only to logged in users?)

By the End:

- Bacheca for Editor - shortcuts to clienti
- Remove Bacheca for clients
- Dashboard for Cliente login - remove access to anything except the profile.php. Remove even the sidebar.
  - Use a plugin to make the edit profile page look ok.
- Move ACF and CPT into programmatic
- Internazionalization With Loco Translator
- Set up permissions. Access to Editor to certain blocks. Check the @TODO
- Use AAM plugin to show/hide content and add redirects. Use ChatGPT.
- Use ASE Plugin at maximum. Use chatGPT for that.
- Create a CSS specific for printing, to hide idle elements of the diet.

Proposals:

- Allow to create custom comments for single Diet as an ACF field in ´diet´, maybe on the sidebar.
- Add plugin for user avatar and sync it with the featured image of `client`

# Testing cases:

- Sync user / client CPT, specially email and try with existing emails.
- Login as
  - client, check the profile update dashboard. visit own diet, try visit others.
  - editor, (nutritionist). Create clients, edit it, create diets ...
  - admin

# Instrucciones

## Para el administrador.

### Crear todos los alimentos

1. Para cada alimento, subir la imagen en el sidebar
2. Rellenar el contenido por defecto

### Crear templates para cada tipo de dieta

1. Crear la diet-category term: /wp-admin/edit-tags.php?taxonomy=diet-category&post_type=diet
2. Crear una template para esa category por default, como plantilla cada vez que se crea una nueva:
   /wp-admin/site-editor.php?categoryId=my-patterns&postType=wp_block
   Puedes usar el Pattern `Giorno Settimana` para ayudarte
3. Asociar la categoria eg. /wp-admin/term.php?taxonomy=diet-category&tag_ID=5&post_type=diet
   con el pattern. Rellenar el campo `Linked Pattern Template` con el slug del pattern que acabas de crear. El slug es predecible a partir del título del Pattern. Si el título es `Dieta Genérica Pattern`, entonces su slug será `dieta-generica-pattern`, que deberá ir en el el campo `Linked Pattern Template`

## Maquetar las page templates del frontend.

1. Las páginas relevantes del frontend son:

- La homepage. Visible sólo si estás logueado - Hace falta maquetar

  - La template asociada a la página. En este caso `page-no-title`: /wp-admin/site-editor.php?postType=wp_template&postId=nutrition-child%2F%2Fpage-no-title&canvas=edit
    Allí hace falta establecer el ancho del contenido, el header de la página si queremos uno, el footer.
  - El contenido de la página en sí. Pagine > Home (/wp-admin/site-editor.php?postType=page&postId=2&canvas=edit)
    Estamos usando las opciones de visibildad para mostrar contenidos diferentes dependiendo de si el usuario está logueado, y de si es `client`, si es `editor` o `administrator` > redirect a Dashboard.

2. Página de una dieta, visible sólo para el cliente, lista para imprimir.
