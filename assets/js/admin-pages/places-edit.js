document.addEventListener("DOMContentLoaded", function () {
  if (typeof CKEDITOR !== "undefined") {
    CKEDITOR.replace("description", {
      language: "vi",
      height: 480,
      removePlugins: "elementspath",
      resize_enabled: true,
      contentsCss:
        document.querySelector('[href*="style.css"]')?.href ||
        "/assets/css/style.css",
      toolbar: [
        {
          name: "document",
          items: ["Source", "-", "Preview"],
        },
        {
          name: "clipboard",
          items: [
            "Cut",
            "Copy",
            "Paste",
            "PasteText",
            "PasteFromWord",
            "-",
            "Undo",
            "Redo",
          ],
        },
        "/",
        {
          name: "basicstyles",
          items: ["Bold", "Italic", "Underline", "Strike", "-", "RemoveFormat"],
        },
        {
          name: "paragraph",
          items: [
            "NumberedList",
            "BulletedList",
            "-",
            "Outdent",
            "Indent",
            "-",
            "Blockquote",
            "-",
            "JustifyLeft",
            "JustifyCenter",
            "JustifyRight",
            "JustifyBlock",
          ],
        },
        {
          name: "links",
          items: ["Link", "Unlink"],
        },
        {
          name: "insert",
          items: ["Image", "Table", "HorizontalRule", "SpecialChar"],
        },
        "/",
        {
          name: "styles",
          items: ["Styles", "Format", "Font", "FontSize"],
        },
        {
          name: "colors",
          items: ["TextColor", "BGColor"],
        },
        {
          name: "tools",
          items: ["Maximize"],
        },
      ],
    });
  }
});
