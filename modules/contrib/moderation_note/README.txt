Moderation Note
================

About this module
------------------
The Moderation Note module allows you to notate moderated entities.

Requirements
-------------
- Content Moderation module
- User module

Configuration
--------------
You can disable email notifications sent by this module by visiting
/admin/config/moderation-note, or Admin -> Configuration -> Moderation Notes.

Usage
------
When viewing the latest revision of a moderated entity, any user with the
"create moderation notes" permission can select text in an entity field and add
a note to it.

Notes can also be assigned to specific users, which adds the note to their
"Assigned notes" tab at their user page or in the toolbar.

Once added, other users can view the note by hovering over highlighted text and
clicking "View note", or by clicking the "View notes" local task.

Users with the create permission can reply to existing notes, which is useful
when discussing feedback.

Once the note has been addressed, the note creator can choose to resolve it by
clicking "Resolve" when viewing the note. Resolved notes can be re-opened or
permanently deleted by clicking the "View notes" local task and viewing the
full resolved note.

Email notifications
--------------------
To inform content editors of new notes, email notifications are sent out in
response to a variety of events:

1. When a note is created, the creator and last-updater of the notated entity
is notified.
2. When a note is assigned to a user, the assignee is notified.
3. When a note is resolved, re-opened, or replied to, the creator and
last-updater of the notated entity, the note assignee (if there is one), and
all users who replied to the note are notified.

When a note is deleted, no one is notified as the resolve notification had
already been sent out.

If you want to customize the email message, your theme can override the
template at templates/mail-moderation-note.html.twig.

You can disable these notifications at /admin/config/moderation-note

Multilingual
-------------
Each note is tied to the langcode of the field being notated. That means that
editors can perform concurrent review of the same content in different
languages.

Note about permissions
-----------------
With a typical Content Moderation setup, you will probably want to give all
users with the "access moderation notes" permission the "view latest version"
and potentially the "view any unpublished content" permission. These allow note
users to access the content being notated if the latest revision is unpublished
and non-default, for example a Draft of a Published Node.
