<!-- Example using Livewire syntax -->
<div>
  <div id="jstree_plans"></div>
</div>

<script>
  $(function () {
    $("#jstree_plans").jstree({
      "core": {
        "data": [
          { "id": "ajson1", "parent": "#", "text": "Simple root node" },
          { "id": "ajson2", "parent": "#", "text": "Root node 2" },
          { "id": "ajson3", "parent": "ajson2", "text": "Child 1" },
          { "id": "ajson4", "parent": "ajson2", "text": "Child 2" },
        ]
      },
      "plugins": [
        "contextmenu", "dnd", "search",
        "state", "types", "wholerow"
      ]
    });
  });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />