<x-filament-widgets::widget>
    <x-filament::section>
        <!-- Example using Livewire syntax -->
        <div>
            <h3>JS Tree</h3>
            

            <div id="jstree_plans"></div>
            
            <div style="padding-top: 20px">
                <div class="col-md-4 col-sm-8 col-xs-8">
                    <button type="button" class="action-button" onclick="createNode();"> Create</button>
                    <button type="button" class="action-button" onclick="renameNode();"> Rename</button>
                    <button type="button" class="action-button" onclick="deleteNode();"> Delete</button>
                    <button type="button" class="action-button" onclick="saveNode();"> Save</button>
                </div>
            </div>
        </div>
    </x-filament::section>

    <style>
        h3 {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .action-button {
            padding: 5px 10px;
            border: 1px solid white;
            margin: 5px;
        }
    </style>
    <script>
        const initialTree = {
            "1": {
                "D": "4"
            },
            "2": "NO FIT",
            "61": {
                "BT": "R",
                "BS": "R",
                "BC": "R",
                "BR": "R",
                "BB": "32"
            },
            "62": {
                "BY": "R"
            }
        };

        document.addEventListener("DOMContentLoaded", function(){
            $("#jstree_plans").jstree({
                "core": {
                    "data": format(initialTree),
                    "check_callback": true,
                },
                "types" : {
                    "#" : {
                        "max_depth" : 4,
                        "valid_children" : ["root"]
                    },
                    "root" : {
                        "icon" : "fa fa-home",
                        "valid_children" : ["folder"]
                    },
                    "folder" : {
                        "max_children": 1,
                        "icon" : "fa fa-folder",
                        "valid_children" : ["file"]
                    },
                    "file" : {
                        "icon" : "fa fa-file",
                        "valid_children" : []
                    }
                },
                "plugins": [
                    "contextmenu", "dnd", "search",
                    "state", "types", "wholerow"
                ]
            });
        });
        
        function createNode() {
            var ref = $('#jstree_plans').jstree(true), sel = ref.get_selected();
            if(!sel.length) { 
                sel = ref.create_node("#", {"type": "root"});
                if (sel) {
                    ref.edit(sel);
                }
            } else {

                sel = sel[0];
                var type = ref.get_type(sel);
                var new_type = "file";
                if (type === "root") {
                    new_type = "folder";
                } else if (type === "folder") {
                    new_type = "file";
                }
                sel = ref.create_node(sel, {"type":new_type});
                if(sel) {
                    ref.edit(sel);
                }
            }
        };
        function renameNode() {
            var ref = $('#jstree_plans').jstree(true),
                sel = ref.get_selected();
            if(!sel.length) { return false; }
            sel = sel[0];
            ref.edit(sel);
        };
        function deleteNode() {
            var ref = $('#jstree_plans').jstree(true),
                sel = ref.get_selected();
            if(!sel.length) { return false; }
            ref.delete_node(sel);
        };

        function saveNode() {
            var ref = $('#jstree_plans').jstree(true);
            var json = ref.get_json('#');
            const validation = validateNode(json);
            if (!validation) {
                alert('Sorry, your tree is invalid');
                return;
            }

            const normalisedJson = normalise(json);
            console.log(normalisedJson);

            // $.post('/plan/save', {
            //     plan: normalisedJson
            // }, () => { alert('Successfully saved!') })
        }

        function normalise(json) {
            const children = json.children;

            if (!json.type) {
                var res = {};
                
                json.forEach(elem => {
                    const child = normalise(elem);

                    if (typeof child === 'string') {
                        res = child;
                    } else {
                        res = {
                            ...res,
                            ...normalise(elem)
                        }
                    }
                });
                return res;
            } else if (!children || children.length === 0) {
                return json.text;
            } else {
                return {
                    [json.text]: normalise(children)
                }
            }
        }

        function format(json, parent = "#") {
            const formatted = [];
            let type = '';
            
            if (parent === '#') {
                type = 'root';
            } else if (parent === 'root') {
                type = 'folder';
            } else {
                type = 'file';
            }

            if (typeof json === 'string') {
                return [{
                    type,
                    text: json
                }]
            }

            for (key in json) {
                const elem = json[key];
                const current = {
                    type,
                    text: key
                };

                const children = format(elem, type);

                formatted.push({
                    ...current,
                    children
                });
            }

            return formatted;
        }

        function validateNode(json) {
            if (json.type === "root") {
                var children = json.children;
                if (children && children.length > 0) {
                    const invalidChildren = children.filter(child => child.type !== "folder");
                    if (invalidChildren.length > 0) return false;

                    const childrenValidation = children.map(child => validateNode(child));
                    if (childrenValidation.filter(valid => !valid).length > 0) return false;

                    return true;
                }
                return true;
            } else if (json.type === "folder") {
                var children = json.children;
                if (!!children) {
                    if (children.length === 0) {
                        if (json.text.toLowerCase() === "no fit") return true;
                        return false;
                    } else if (children.length === 1) {
                        if (children[0].type === "file") {
                            if (json.text.toLowerCase() === "no fit") return false;
                            return validateNode(children[0]);
                        }
                        return false;
                    } else {
                        return false;
                    }
                }
                return true;
            } else if (json.type === "file") {
                var children = json.children;
                if (!!children) {
                    return children.length === 0;
                }
                return true;
            } else if (!json.type) {
                var children = json;
                if (children && children.length > 0) {
                    const invalidChildren = children.filter(child => child.type !== "root");
                    if (invalidChildren.length > 0) return false;

                    const childrenValidation = children.map(child => validateNode(child));
                    if (childrenValidation.filter(valid => !valid).length > 0) return false;

                    return true;
                }
                return true;
            } else {
                return false;
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/themes/default/style.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/jstree.min.js"></script>
</x-filament-widgets::widget>