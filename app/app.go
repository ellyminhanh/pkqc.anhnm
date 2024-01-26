package app

import (
	"github.com/labstack/echo/v4"
	"github.com/labstack/echo/v4/middleware"
	"noads/providers"
)

type Application struct {
	server *echo.Echo
	p      *providers.Provider
}

func NewApplication() *Application {
	server := echo.New()
	server.Use(middleware.Logger())
	ap := &Application{
		server: server,
	}

	ap.p = providers.NewProvider()
	ap.initRouters()
	return ap
}

// Init init routers
func (a *Application) initRouters() {
	a.server.GET("/", func(c echo.Context) error {
		return c.String(200, "Hello, World!")
	})
	apiGroup := a.server.Group("/api")
	apiGroup.GET("/view", func(c echo.Context) error {
		url := c.QueryParam("url")
		if url == "" {
			return c.JSON(400, "url is required")
		}
		data, err := a.p.GetLink(url)
		if err != nil {
			return c.JSON(400, err)
		}
		return c.JSON(200, data)
	})
}

// Run server
func (a *Application) Run() error {
	return a.server.Start(":8080")
}
