//
//  MapViewController.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import UIKit
import SKMaps

class MapViewController: MainViewController {

    @IBOutlet weak var mapView: SKMapView!
    
    var currentUserLocation: CLLocation?
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
        configureMap()
        configureNavigationBar()
    }
    
    func configureNavigationBar() {
        let logoImage = UIImage.init(named: "zoomtivity_logo")
        let logoImageView = UIImageView.init(image: logoImage, highlightedImage: logoImage)
        logoImageView.contentMode = .scaleAspectFit
        
        if let navigationBar = self.navigationController?.navigationBar {
            logoImageView.frame.origin.x = navigationBar.frame.size.width * 0.175
            logoImageView.frame.origin.y = 0
            logoImageView.frame.size.width = navigationBar.frame.size.width * 0.3
            logoImageView.frame.size.height = navigationBar.frame.size.height
            navigationBar.addSubview(logoImageView)
        }
    }

    func configureMap() {
        mapView.settings.followUserPosition = false
        mapView.settings.rotationEnabled = false
        mapView.settings.showCompass = false
        mapView.delegate = self
        mapView.mapScaleView.isHidden = true
        
    
        SKPositionerService.sharedInstance().delegate = self
        SKPositionerService.sharedInstance().startLocationUpdate()
    }
    
    func getFoodForCurrentMapPosition() {
        let northEastCoords = mapView.coordinate(for: CGPoint.init(x: mapView.bounds.size.width - 1, y: 1))
        let southWestCoords = mapView.coordinate(for: CGPoint.init(x: 1, y: mapView.bounds.size.height - 1))
        DatabaseManager.sharedDataManager.fetchPoints(type: "food",
                                                      southWestPoint: southWestCoords,
                                                      northEastPoint: northEastCoords,
                                                      completion: { points in
                                                 self.placePointsOnMap(points: points)
        })
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}



