import { Box, Container, Paper, Stack, Typography } from "@mui/material";
import type { ReactNode } from "react";

type AdminPageShellProps = {
    title: string;
    subtitle: string;
    action?: ReactNode;
    children: ReactNode;
};

export function AdminPageShell({ title, subtitle, action, children }: AdminPageShellProps) {
    return (
        <Container maxWidth="lg" sx={{ py: { xs: 3, md: 5 } }}>
            <Paper
                elevation={0}
                sx={{
                    p: { xs: 2, md: 3 },
                    borderRadius: 4,
                    mb: 3,
                    border: "1px solid",
                    borderColor: "divider",
                    background: "linear-gradient(135deg, #ffffff 0%, #ecfeff 100%)",
                }}
            >
                <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: 2, flexWrap: "wrap" }}>
                    <Box>
                        <Typography variant="h4" sx={{ fontWeight: 800 }}>{title}</Typography>
                        <Typography color="text.secondary">{subtitle}</Typography>
                    </Box>
                    {action}
                </Box>
            </Paper>

            <Stack spacing={2.5}>{children}</Stack>
        </Container>
    );
}

export function AdminSurface({ children }: { children: ReactNode }) {
    return (
        <Paper
            elevation={0}
            sx={{
                p: { xs: 2, md: 2.5 },
                borderRadius: 3,
                border: "1px solid",
                borderColor: "divider",
                boxShadow: "0 10px 30px rgba(15,23,42,0.06)",
            }}
        >
            {children}
        </Paper>
    );
}
