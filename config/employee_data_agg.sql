INSERT INTO EmployeeDataAggregated (
    EmployeeNumber,
    FirstName,
    LastName,
    OrgLevel2Code,
    OrgLevel2,
    DateReceived,
    LicenseCertificationCode,
    LicenseCertification,
    Number,
    TypeCode,
    ProviderCode,
    RenewalDate
)
SELECT * FROM (
    SELECT
        EmployeeNumber,
        GROUP_CONCAT(DISTINCT FirstName SEPARATOR ', ') AS FirstName,
        GROUP_CONCAT(DISTINCT LastName SEPARATOR ', ') AS LastName,
        GROUP_CONCAT(DISTINCT OrgLevel2Code SEPARATOR ', ') AS OrgLevel2Code,
        GROUP_CONCAT(DISTINCT OrgLevel2 SEPARATOR ', ') AS OrgLevel2,
        GROUP_CONCAT(DISTINCT DateReceived SEPARATOR ', ') AS DateReceived,
        GROUP_CONCAT(DISTINCT `LicenseCertificationCode` SEPARATOR ', ') AS `LicenseCertificationCode`,
        GROUP_CONCAT(DISTINCT `LicenseCertification` SEPARATOR ', ') AS `LicenseCertification`,
        GROUP_CONCAT(
            DISTINCT 
            CASE 
                WHEN TypeCode IN ('CDL', 'CDLB', 'CDLC', 'CHAUFL', 'CLPA', 'DL', 'ID') 
                THEN Number 
                ELSE NULL 
            END 
            SEPARATOR ', '
        ) AS Number,
        GROUP_CONCAT(
            DISTINCT 
            CASE 
                WHEN TypeCode IN ('AU1026', 'AU10K', 'AU21', 'AU26', 'UNAUTH') 
                THEN TypeCode 
                ELSE NULL 
            END 
            SEPARATOR ', '
        ) AS TypeCode,
        GROUP_CONCAT(DISTINCT ProviderCode SEPARATOR ', ') AS ProviderCode,
        GROUP_CONCAT(DISTINCT RenewalDate SEPARATOR ', ') AS RenewalDate
    FROM
        EmployeeLicenses
    GROUP BY
        EmployeeNumber
) AS new_values
ON DUPLICATE KEY UPDATE
    FirstName = new_values.FirstName,
    LastName = new_values.LastName,
    OrgLevel2Code = new_values.OrgLevel2Code,
    OrgLevel2 = new_values.OrgLevel2,
    DateReceived = new_values.DateReceived,
    LicenseCertificationCode = new_values.LicenseCertificationCode,
    LicenseCertification = new_values.LicenseCertification,
    Number = new_values.Number,
    TypeCode = new_values.TypeCode,
    ProviderCode = new_values.ProviderCode,
    RenewalDate = new_values.RenewalDate;
