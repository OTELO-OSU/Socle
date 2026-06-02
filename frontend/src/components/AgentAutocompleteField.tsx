import { Autocomplete, TextField } from "@mui/material";
import { useCallback, useEffect, useMemo, useState } from "react";
import { getAgentByPage, type Agent } from "../services/agentService";

type AgentAutocompleteBaseProps = {
    label: string;
    enabled?: boolean;
};

type AgentAutocompleteFieldMultipleProps = AgentAutocompleteBaseProps & {
    multiple?: true;
    value: Agent[];
    onChange: (value: Agent[]) => void;
};

type AgentAutocompleteFieldSingleProps = AgentAutocompleteBaseProps & {
    multiple?: false;
    value: Agent | null;
    onChange: (value: Agent | null) => void;
};

type AgentAutocompleteFieldProps = AgentAutocompleteFieldMultipleProps | AgentAutocompleteFieldSingleProps;

function getAgentLabel(agent: Agent) {
    return `${agent.nom} ${agent.prenom}`;
}

export function AgentAutocompleteField({
    label,
    value: rawValue,
    onChange: rawOnChange,
    multiple = false,
    enabled = true,
}: AgentAutocompleteFieldProps) {
    const selectedAgents = useMemo(
        () => (Array.isArray(rawValue) ? rawValue : (rawValue ? [rawValue] : [])),
        [rawValue],
    );
    const [options, setOptions] = useState<Agent[]>(selectedAgents);
    const [inputValue, setInputValue] = useState("");
    const [debouncedSearch, setDebouncedSearch] = useState("");
    const [loading, setLoading] = useState(false);

    const mergeAgents = useCallback((agents: Agent[], selected: Agent[]) => {
        const map = new Map<number, Agent>();
        for (const item of selected) map.set(item.id!, item);
        for (const item of agents) map.set(item.id!, item);
        return Array.from(map.values());
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(inputValue);
        }, 500);

        return () => clearTimeout(timer);
    }, [inputValue]);

    useEffect(() => {
        setOptions((previousOptions) => mergeAgents(previousOptions, selectedAgents));
    }, [mergeAgents, selectedAgents]);

    useEffect(() => {
        if (!enabled) {
            return;
        }

        let isMounted = true;

        const loadAgents = async () => {
            setLoading(true);
            try {
                const { agents } = await getAgentByPage(debouncedSearch);

                if (isMounted) {
                    setOptions(mergeAgents(agents, selectedAgents));
                }
            } catch (error) {
                console.error("Erreur lors du chargement des agents :", error);
            } finally {
                if (isMounted) {
                    setLoading(false);
                }
            }
        };

        void loadAgents();

        return () => {
            isMounted = false;
        };
    }, [debouncedSearch, enabled, mergeAgents, selectedAgents]);

    return (
        <Autocomplete
            multiple={multiple}
            noOptionsText="Aucun agent trouvé"
            options={options}
            value={rawValue as Agent[] | Agent | null}
            loading={loading}
            inputValue={inputValue}
            onInputChange={(_, newValue) => setInputValue(newValue)}
            onChange={(_, newValue) => {
                if (multiple) {
                    (rawOnChange as (next: Agent[]) => void)(newValue as Agent[]);
                    return;
                }

                (rawOnChange as (next: Agent | null) => void)(newValue as Agent | null);
            }}
            getOptionLabel={getAgentLabel}
            isOptionEqualToValue={(option, selectedValue) => option.id === selectedValue.id}
            filterOptions={(items) => items}
            renderInput={(params) => (
                <TextField
                    {...params}
                    label={label}
                    placeholder="Rechercher un agent"
                />
            )}
        />
    );
}