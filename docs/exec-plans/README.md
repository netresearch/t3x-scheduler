# Execution Plans

Working directory for multi-step agent execution plans (design docs, migration plans, task breakdowns).

- `active/` — plans currently being executed. Create the directory with the first plan.
- `completed/` — plans that shipped; keep them for archaeology, they explain why code looks the way it does.

Conventions: one Markdown file per plan, named `YYYY-MM-DD-<slug>.md`; move the file from `active/` to `completed/` when the work merges. A plan is a hypothesis — re-verify its assumptions against the code before executing an old one.
