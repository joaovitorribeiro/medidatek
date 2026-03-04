# Prompt — Landing Page MedidaTeck (Laravel + Inertia + Vue 3 + TS + Tailwind + IA)

Você é um(a) especialista em conversão, UX/UI e engenharia full-stack (Laravel + Inertia.js + Vue 3 + TypeScript + TailwindCSS). Crie uma landing page completa para a **MedidaTeck**, empresa de **sistemas sob medida com IA** que gera **resultados reais**, aumenta **vendas** e otimiza **processos**.

## Objetivo principal
Maximizar conversão para:
- “Solicitar orçamento”
- “Falar com especialista”
- “Teste sua ideia gratuitamente”

## Diretrizes de persuasão (obrigatório)
Aplicar:
- Prova social (logos, depoimentos, métricas)
- Autoridade (processo claro, stack moderna, linguagem segura)
- Reciprocidade (diagnóstico inicial, checklist)
- Consistência (microcompromissos antes do formulário completo)
- Urgência/Escassez (somente se for verdadeiro; sem promessas inventadas)

## Design e UI (obrigatório)
- Design moderno, responsivo, com hierarquia visual clara.
- CTAs com alto contraste e foco.
- Microinterações (hover/press, animações leves, transitions).
- Acessibilidade (contraste AA, foco visível, labels).

## Estrutura de seções (obrigatória)
1) Hero
   - Título direto com benefício mensurável.
   - Subtítulo com resultado tangível.
   - CTA principal destacado (“Solicite seu orçamento agora”).
   - CTA secundário (“Fale com especialista” ou “Teste sua ideia gratuitamente”).
2) Prova social
   - Depoimentos (curtos e específicos).
   - Logos de clientes (placeholder se necessário).
   - Cards com métricas (placeholder se necessário).
3) Serviços e métodos
   - Mostrar impacto direto em vendas/produtividade.
   - Destacar IA aplicada: chat, recomendações, personalização e insights.
   - Explicar o método: diagnóstico → planejamento → ágil → testes → entrega/suporte.
4) Diferenciais
   - Personalização real, UX/UI que converte, integrações, IA aplicada, agilidade com qualidade.
5) CTA secundário (bloco próprio)
   - Reforço de valor (“Receba um diagnóstico inicial”).
   - Formulário com poucos campos.
6) FAQ
   - Custo, prazo, integrações, suporte e segurança/privacidade da IA.
7) Rodapé
   - Contatos, links rápidos e reforço da proposta de valor.

## IA (obrigatório no escopo)
Integrar IA para:
- Chat interativo (pré-vendas) com direcionamento para CTAs.
- Recomendação de serviço com base em 2–4 respostas.
- Personalização controlada de microcopy (somente variações aprovadas).
- Insights de comportamento (em lote; sem dados sensíveis).

Regras:
- Não solicitar nem enviar dados sensíveis ao provedor de IA.
- Implementar rate limit e logs de auditoria de custo/latência.

## Backend (Laravel)
Entregar:
- Rotas/endpoints para chat, recomendação, copy e captura de leads.
- Validações do formulário e proteção anti-spam.
- Integração assíncrona com CRM/automação (fila/jobs).

## Frontend (Vue 3 + TS + Inertia + Tailwind)
Entregar:
- Página de landing como componente Inertia.
- Componentes reutilizáveis para seções (Hero, SocialProof, Services, Method, Differentials, CTA, FAQ, Footer).
- Widget de chat e recomendação (UI pronta + chamada aos endpoints).

## SEO e performance
- Meta tags, Open Graph, headings corretos, FAQ schema (se possível).
- Otimização de imagens e carregamento rápido.

## Saída esperada
Forneça:
- Copy completa em pt-BR (títulos, subtítulos, CTAs e microcopy).
- Estrutura visual (layout) e classes Tailwind.
- Pseudo-contratos dos endpoints e modelo de dados mínimo.
- Checklist final de QA (conversão, responsivo, acessibilidade, SEO e performance).

