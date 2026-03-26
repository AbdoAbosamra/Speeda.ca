## Laravel MCP

- MCP (Model Context Protocol) is very new. You must use the ___SINGLE_BACKTICK___search-docs___SINGLE_BACKTICK___ tool to get documentation for how to write and test Laravel MCP servers, tools, resources, and prompts effectively.
- MCP servers need to be registered with a route or handle in ___SINGLE_BACKTICK___routes/ai.php___SINGLE_BACKTICK___. Typically, they will be registered using ___SINGLE_BACKTICK___Mcp::web()___SINGLE_BACKTICK___ to register a HTTP streaming MCP server.
- Servers are very testable - use the ___SINGLE_BACKTICK___search-docs___SINGLE_BACKTICK___ tool to find testing instructions.
- Do not run ___SINGLE_BACKTICK___mcp:start___SINGLE_BACKTICK___. This command hangs waiting for JSON RPC MCP requests.
- Some MCP clients use Node, which has its own certificate store. If a user tries to connect to their web MCP server locally using https://, it could fail due to this reason. They will need to switch to http:// during local development.
<?php /**PATH Y:\Speeda - Versions\Speeda\storage\framework\views/b83611c8d62ef6032a9f0a16b6409458.blade.php ENDPATH**/ ?>