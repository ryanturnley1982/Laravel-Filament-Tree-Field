<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <input type="hidden" x-model="state" id="tree_result">
    </div>
</x-dynamic-component>
<div id="jstree_wrapper">
<div id="jstree_plans"></div>
</div>

@if($this->getContentTabLabel() != 'View')
<div style="padding-top: 20px">
    <div class="col-md-4 col-sm-8 col-xs-8">
        <button type="button" class="action-button" onclick="createNode();"> Create Node</button>
        <button type="button" class="action-button" onclick="renameNode();"> Rename Node</button>
        <button type="button" class="action-button" onclick="deleteNode();"> Delete Node</button>
    </div>
</div>
@endif


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

        #tree_result {
            color: black;
        }

        input {
            color: black;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            let treeElem = document.getElementById('jstree_plans');
            const treeWrapper = document.getElementById('jstree_wrapper');
            const inputElem = document.getElementById('tree_result');

            const syncTree = async () => {
                const treeData = treeElem.getAttribute('role');
                const inputData = inputElem.value;

                if (treeData !== 'tree') {

                    treeElem.remove();
                    treeElem = document.createElement('div')
                    treeElem.id = "jstree_plans";
                    treeWrapper.appendChild(treeElem);

                    let initialTree = null;
            
                    try {
                        const initialData = !!inputData ? inputData : '<?= $getRecord() ? $getRecord()[array_reverse(explode('.', $getStatePath()))[0]] : '{}'?>';
                        initialTree = JSON.parse(initialData)
                    } catch (err) {
                        console.log(err)
                    }

                    try {
                        $('#jstree_plans').jstree('destroy');
                        
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
                                "state", 
                                "types", 
                                "wholerow"
                            ]
                        });
                    } catch (err) {
                        console.log(err)
                    }

                    $('#jstree_plans').on("set_text.jstree", (obj, text) => {
                        nodeUpdated();
                    });

                    $('#jstree_plans').on("create_node.jstree", (obj, text) => {
                        nodeUpdated();
                    });

                    $('#jstree_plans').on("delete_node.jstree", (obj, text) => {
                        nodeUpdated();
                    });
                }

            }
            const treeObserver = new MutationObserver(syncTree);
            treeObserver.observe(treeWrapper, { attributes: true, childList: false, subtree: true, attributeFilter: ['role'] })
            syncTree();
        });

        document.getElementById('jstree_plans').addEventListener('alpine:init', (e) => {
            console.log('changed')
        })
        
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

        function nodeUpdated() {
            var ref = $('#jstree_plans').jstree(true);
            var json = ref.get_json('#');
            const validation = validateNode(json);
            if (!validation) {
                return;
            }

            const normalisedJson = normalise(json);
            $('#tree_result').val(JSON.stringify(normalisedJson));
            
            var element = document.getElementById('tree_result');
            element.dispatchEvent(new Event('input'));
            
        }

        function saveNode() {
            var ref = $('#jstree_plans').jstree(true);
            var json = ref.get_json('#');
            const validation = validateNode(json);
            if (!validation) {
                alert('Sorry, your tree is invalid');
                return;
            }

            const normalisedJson = normalise(json);
            console.log(normalisedJson)
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

            if (!json) {
                return null;
            }
            
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

    <link rel="stylesheet" href="/css/style.min.css" />
    <link rel="stylesheet" href="/css/font-awesome.css">
    <script src="/js/jquery.min.js"></script>
    <script src="/js/jstree.min.js"></script>