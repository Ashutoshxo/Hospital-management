



In development, credentials are stored in `.env` file:

CCAVENUE_MERCHANT_ID=test_merchant_123
CCAVENUE_ACCESS_CODE=test_access_code_456
CCAVENUE_WORKING_KEY=test_working_key_789ABC




####  AWS Secrets Manager

aws secretsmanager create-secret \
    --name hospital-system/ccavenue \
    --secret-string '{
        "merchant_id": "live_merchant_xyz",
        "access_code": "live_access_abc",
        "working_key": "live_key_secure_123"
    }'



## AWS Deployment Strategy

Deploy on EC2 with Application Load Balancer across multiple AZs. Use Auto Scaling Group (min: 2, max: 10 instances). Database on RDS with automated backups and point-in-time recovery.

### IAM Role for EC2

{
    "Effect": "Allow",
    "Action": ["secretsmanager:GetSecretValue"],
    "Resource": "arn:aws:secretsmanager:ap-south-1:*:secret:hospital-system/*"
}
