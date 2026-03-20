<?php
class TenantManager {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getCurrentTenantId() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // If Super Admin checks for specific tenant context (Impersonation)
        if ($this->isSuperAdmin()) {
            return isset($_SESSION['impersonate_tenant_id']) ? $_SESSION['impersonate_tenant_id'] : null;
        }

        // Standard behavior: return the logged-in user's tenant_id
        return isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;
    }

    public function isSuperAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Helper to append tenant_id to WHERE clauses
    // Usage: $query .= $tm->getTenantWhereClause(); 
    // Or: $query .= " AND " . $tm->getTenantWhereClause(false);
    public function getTenantWhereClause($tableAliasOrIncludeWhere = true, $includeWhere = true) {
        $tablePrefix = "";
        $useWhere = $includeWhere;

        if (is_string($tableAliasOrIncludeWhere)) {
            $tablePrefix = $tableAliasOrIncludeWhere . ".";
            // If first arg is string, second arg controls WHERE
        } elseif (is_bool($tableAliasOrIncludeWhere)) {
            $useWhere = $tableAliasOrIncludeWhere;
        }

        $tenantId = $this->getCurrentTenantId();
        if ($this->isSuperAdmin() && !isset($_SESSION['impersonate_tenant_id'])) {
            return ""; 
        }
        
        if ($tenantId === null) {
            return $useWhere ? " WHERE 1=0 " : " 1=0 ";
        }

        return ($useWhere ? " WHERE " : "") . " {$tablePrefix}tenant_id = '$tenantId' ";
    }
}
?>
