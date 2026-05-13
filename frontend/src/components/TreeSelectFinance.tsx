import { useContext, useMemo, useState } from "react";
import {
    Autocomplete,
    Box,
    Popover,
    TextField,
} from "@mui/material";
import {
    SimpleTreeView,
    TreeItem,
} from "@mui/x-tree-view";
import { DictionnaireContext } from "../context/dictionnaireContext";

type TreeNode = {
    id: number;
    label: string;
    children?: TreeNode[];
};

type TreeSelectProps = {
    selectedId?: number;
    onSelectedIdChange?: (id: number) => void;
};

function flattenNodes(nodes: TreeNode[]): TreeNode[] {
    return nodes.flatMap((node) => [
        { id: node.id, label: node.label },
        ...(node.children ? flattenNodes(node.children) : []),
    ]);
}

function createNodeMap(nodes: TreeNode[]): Record<number, TreeNode> {
    return nodes.reduce<Record<number, TreeNode>>((acc, node) => {
        acc[node.id] = node;

        if (node.children) {
            Object.assign(acc, createNodeMap(node.children));
        }

        return acc;
    }, {});
}

function filterTree(nodes: TreeNode[], query: string): TreeNode[] {
    const normalizedQuery = query.trim().toLowerCase();

    if (!normalizedQuery) {
        return nodes;
    }

    return nodes.reduce<TreeNode[]>((acc, node) => {
        const children = node.children
            ? filterTree(node.children, normalizedQuery)
            : undefined;

        const currentMatches =
            node.label.toLowerCase().includes(normalizedQuery) ||
            node.id.toString().includes(normalizedQuery);

        if (currentMatches || (children && children.length > 0)) {
            acc.push({
                ...node,
                children,
            });
        }

        return acc;
    }, []);
}

export default function TreeSelect({
    selectedId,
    onSelectedIdChange,
}: TreeSelectProps) {
    const [anchorEl, setAnchorEl] =
        useState<HTMLElement | null>(null);

    const [searchValue, setSearchValue] = useState("");
    const [internalSelectedId, setInternalSelectedId] =
        useState<number | undefined>(undefined);

    const dictionnaireContext = useContext(DictionnaireContext);
    const TREE_DATA = dictionnaireContext.financeurTreeView;

    const allNodes = useMemo(() => flattenNodes(TREE_DATA), [TREE_DATA]);

    const nodeById = useMemo(() => createNodeMap(TREE_DATA), [TREE_DATA]);

    const currentSelectedId = selectedId ?? internalSelectedId;

    const currentSelectedNode =
        currentSelectedId !== undefined && !nodeById[currentSelectedId]?.children?.length
            ? nodeById[currentSelectedId]
            : undefined;

    const filteredTree = useMemo(
        () => filterTree(TREE_DATA, searchValue),
        [TREE_DATA, searchValue],
    );

    const isOpen = Boolean(anchorEl);

    const displayedInputValue = isOpen
        ? searchValue
        : currentSelectedNode?.label || searchValue;

    const renderTree = (nodes: TreeNode[]) =>
        nodes.map((node) => (
            <TreeItem key={node.id} itemId={node.id.toString()} label={node.label}>
                {node.children ? renderTree(node.children) : null}
            </TreeItem>
        ));

    return (
        <>
            <Autocomplete
                fullWidth
                freeSolo
                options={allNodes.map((node) => `${node.label} (${node.id})`)}
                open={false}
                value={null}
                inputValue={displayedInputValue}
                onInputChange={(_, newInputValue) => {
                    setSearchValue(newInputValue);
                }}
                forcePopupIcon={false}
                renderInput={(params) => (
                    <TextField
                        {...params}
                        label="Source de financement"
                        onClick={(e) => {
                            setAnchorEl(e.currentTarget);
                            setSearchValue("");
                        }}
                    />
                )}
            />

            <Popover
                open={isOpen}
                anchorEl={anchorEl}
                onClose={() => setAnchorEl(null)}
                disableAutoFocus
                disableEnforceFocus
                disableRestoreFocus
                anchorOrigin={{
                    vertical: "bottom",
                    horizontal: "left",
                }}
            >
                <Box sx={{ p: 1, width: 300 }}>
                    {filteredTree.length > 0 ? (
                        <SimpleTreeView
                            selectedItems={currentSelectedId !== undefined ? currentSelectedId.toString() : ""}
                            onSelectedItemsChange={(_, itemId) => {
                                const nextIdAsString =
                                    typeof itemId === "string"
                                        ? itemId
                                        : itemId?.[0] || "";

                                if (!nextIdAsString) {
                                    return;
                                }

                                const nextId = Number(nextIdAsString);

                                if (Number.isNaN(nextId)) {
                                    return;
                                }

                                const selectedNode = nodeById[nextId];

                                if (!selectedNode || selectedNode.children?.length) {
                                    return;
                                }

                                if (selectedId === undefined) {
                                    setInternalSelectedId(nextId);
                                }

                                setSearchValue(selectedNode.label);
                                onSelectedIdChange?.(nextId);
                                setAnchorEl(null);
                            }}
                        >
                            {renderTree(filteredTree)}
                        </SimpleTreeView>
                    ) : (
                        <Box sx={{ p: 1, color: "text.secondary" }}>
                            Aucun resultat trouvé
                        </Box>
                    )}
                </Box>
            </Popover>
        </>
    );
}