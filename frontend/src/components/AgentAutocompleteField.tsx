import { Autocomplete, TextField } from "@mui/material";
import { useCallback, useEffect, useState } from "react";
import { getAgentByPage, type Agent } from "../services/agentService";

type AgentAutocompleteFieldProps = {
    label: string;
    value: Agent[];
    onChange: (value: Agent[]) => void;
    enabled?: boolean;
};

function getAgentLabel(agent: Agent) {
    return `${agent.nom} ${agent.prenom}`;
}

export function AgentAutocompleteField({
    label,
    value,
    onChange,
    enabled = true,
}: AgentAutocompleteFieldProps) {
    const [options, setOptions] = useState<Agent[]>(value);
    const [inputValue, setInputValue] = useState("");
    const [debouncedSearch, setDebouncedSearch] = useState("");
    const [loading, setLoading] = useState(false);

    const mergeAgents = useCallback((agents: Agent[], selected: Agent[]) => {
        const map = new Map<number, Agent>();
        for (const item of selected) map.set(item.id, item);
        for (const item of agents) map.set(item.id, item);
        return Array.from(map.values());
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(inputValue);
        }, 500);

        return () => clearTimeout(timer);
    }, [inputValue]);

    useEffect(() => {
        setOptions((previousOptions) => mergeAgents(previousOptions, value));
    }, [mergeAgents, value]);

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
                    setOptions(mergeAgents(agents, value));
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
    }, [debouncedSearch, enabled, mergeAgents, value]);

    return (
        <Autocomplete
            multiple
            noOptionsText="Aucun agent trouvé"
            options={options}
            value={value}
            loading={loading}
            inputValue={inputValue}
            onInputChange={(_, newValue) => setInputValue(newValue)}
            onChange={(_, newValue) => onChange(newValue)}
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