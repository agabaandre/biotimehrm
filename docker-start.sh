#!/bin/bash

# Docker Management Script for Attendance Management System

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    print_success "Docker is running"
}

# Function to check if Docker Compose is available
check_docker_compose() {
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install it and try again."
        exit 1
    fi
    print_success "Docker Compose is available"
}

# Function to check environment file
check_environment() {
    if [ ! -f "docker/.env" ]; then
        print_warning "Environment file not found. Creating from example..."
        if [ -f "docker/env.example" ]; then
            cp docker/env.example docker/.env
            print_warning "Please edit docker/.env with your database credentials"
            print_warning "For host machine database, use DB_HOST=host.docker.internal"
            print_warning "For remote database, use the actual IP/hostname"
        else
            print_error "Environment example file not found. Please create docker/.env manually"
            exit 1
        fi
    fi
}

# Function to start services
start_services() {
    print_status "Starting Docker services..."
    docker-compose up -d --build
    
    if [ $? -eq 0 ]; then
        print_success "Services started successfully"
        print_status "Waiting for services to be ready..."
        sleep 10
        
        # Check service status
        docker-compose ps
        
        print_success "All services are running!"
        print_status "Access the application at: http://localhost:8080"
        print_status "Redis: localhost:6379"
        print_status ""
        print_status "Note: Database connection depends on your docker/.env configuration"
        print_status "Use 'host.docker.internal' if database is on the host machine"
    else
        print_error "Failed to start services"
        exit 1
    fi
}

# Function to stop services
stop_services() {
    print_status "Stopping Docker services..."
    docker-compose down
    
    if [ $? -eq 0 ]; then
        print_success "Services stopped successfully"
    else
        print_error "Failed to stop services"
        exit 1
    fi
}

# Function to restart services
restart_services() {
    print_status "Restarting Docker services..."
    docker-compose restart
    
    if [ $? -eq 0 ]; then
        print_success "Services restarted successfully"
    else
        print_error "Failed to restart services"
        exit 1
    fi
}

# Function to view logs
view_logs() {
    print_status "Showing logs for all services..."
    docker-compose logs -f
}

# Function to view logs for specific service
view_service_logs() {
    if [ -z "$1" ]; then
        print_error "Please specify a service name (e.g., php, nginx, cron, redis)"
        exit 1
    fi
    
    print_status "Showing logs for service: $1"
    docker-compose logs -f "$1"
}

# Function to access container shell
access_shell() {
    if [ -z "$1" ]; then
        print_error "Please specify a service name (e.g., php, nginx, cron, redis)"
        exit 1
    fi
    
    print_status "Accessing shell for service: $1"
    docker-compose exec "$1" bash
}

# Function to show status
show_status() {
    print_status "Service status:"
    docker-compose ps
}

# Function to test database connection
test_database() {
    print_status "Testing database connection from PHP container..."
    docker-compose exec php php -r "
        \$host = getenv('DB_HOST') ?: 'host.docker.internal';
        \$db = getenv('DB_NAME') ?: 'attend';
        \$user = getenv('DB_USER') ?: 'your_db_user';
        \$pass = getenv('DB_PASS') ?: 'your_db_password';
        \$port = getenv('DB_PORT') ?: '3306';
        
        try {
            \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
            echo \"Database connection successful!\n\";
            echo \"Host: \$host\n\";
            echo \"Database: \$db\n\";
            echo \"User: \$user\n\";
        } catch (PDOException \$e) {
            echo \"Database connection failed: \" . \$e->getMessage() . \"\n\";
        }
    "
}

# Function to show help
show_help() {
    echo "Docker Management Script for Attendance Management System"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  start           Start all services"
    echo "  stop            Stop all services"
    echo "  restart         Restart all services"
    echo "  status          Show service status"
    echo "  logs            Show logs for all services"
    echo "  logs [SERVICE]  Show logs for specific service"
    echo "  shell [SERVICE] Access shell for specific service"
    echo "  test-db         Test database connection"
    echo "  help            Show this help message"
    echo ""
    echo "Services:"
    echo "  php             PHP-FPM service"
    echo "  nginx           Web server (port 8080)"
    echo "  cron            Background job processor"
    echo "  redis           Caching service (port 6379)"
    echo ""
    echo "Examples:"
    echo "  $0 start                    # Start all services"
    echo "  $0 logs php                 # Show PHP service logs"
    echo "  $0 shell php                # Access PHP container shell"
    echo "  $0 test-db                  # Test database connection"
    echo ""
    echo "Note: Database connection uses docker/.env configuration"
    echo "Use 'host.docker.internal' for host machine database"
}

# Main script logic
case "$1" in
    start)
        check_docker
        check_docker_compose
        check_environment
        start_services
        ;;
    stop)
        check_docker
        check_docker_compose
        stop_services
        ;;
    restart)
        check_docker
        check_docker_compose
        restart_services
        ;;
    status)
        check_docker
        check_docker_compose
        show_status
        ;;
    logs)
        check_docker
        check_docker_compose
        if [ -z "$2" ]; then
            view_logs
        else
            view_service_logs "$2"
        fi
        ;;
    shell)
        check_docker
        check_docker_compose
        access_shell "$2"
        ;;
    test-db)
        check_docker
        check_docker_compose
        test_database
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
